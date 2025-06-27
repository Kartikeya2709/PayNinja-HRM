<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\BeneficiaryBadge;
use App\Models\EmployeeSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // For potential transaction
use Illuminate\Validation\Rule;

class EmployeePayrollConfigController extends Controller
{
    /**
     * Display a listing of employees to configure their payroll.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $employees = Employee::where('company_id', $companyId)
                               ->with('user', 'employeeDetail', 'employeeSalaries') // Eager load for display
                               ->latest()
                               ->paginate(15);

        return view('admin.payroll.employee-configurations.index', compact('employees'));
    }

    /**
     * Show the form for editing the specified employee's payroll configuration.
     */
    public function edit(Employee $employee)
    {
        $companyId = Auth::user()->company_id;
        if ($employee->company_id !== $companyId) {
            abort(403, 'Unauthorized action. Employee does not belong to your company.');
        }

        // Eager load necessary details with salary data
        $employee->load([
            'employeeDetail',
            'assignedBeneficiaryBadges.beneficiaryBadge',
            'currentSalary',
            'employeeSalaries'
        ]);

        // Get the salary to display (either from salary_id parameter or current salary)
        $currentSalary = null;
        
        if (request()->has('salary_id')) {
            $currentSalary = $employee->employeeSalaries->find(request('salary_id'));
        }
        
        // If no salary found from salary_id or no salary_id provided, use current salary or create new
        if (!$currentSalary) {
            $currentSalary = $employee->currentSalary ?? new EmployeeSalary([
                'employee_id' => $employee->id,
                'is_current' => true,
                'status' => 'active',
                'currency' => $employee->company->default_currency ?? config('app.currency', 'USD'),
                'payment_frequency' => 'monthly',
                'effective_from' => now()
            ]);
        }

        $availableBadges = BeneficiaryBadge::where('company_id', $companyId)
                                           ->where('is_active', true)
                                           ->orderBy('type')
                                           ->orderBy('name')
                                           ->get();

        // Prepare a map of assigned badges for easier access in the view
        $assignedBadgesMap = $employee->assignedBeneficiaryBadges->keyBy('beneficiary_badge_id');

        return view('admin.payroll.employee-configurations.edit', [
            'employee' => $employee,
            'availableBadges' => $availableBadges,
            'assignedBadgesMap' => $assignedBadgesMap,
            'currentSalary' => $currentSalary
        ]);
    }

    /**
     * Update the specified employee's payroll configuration in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        \Log::info('🚀 Starting employee payroll config update', [
            'employee_id' => $employee->id,
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $request->except(['_token', '_method'])
        ]);
    
        $companyId = Auth::user()->company_id;
    
        if ($employee->company_id !== $companyId) {
            $error = 'Unauthorized action. Employee does not belong to your company.';
            \Log::error('❌ Company mismatch', [
                'employee_company_id' => $employee->company_id,
                'user_company_id' => $companyId,
                'employee_id' => $employee->id,
                'user_id' => Auth::id()
            ]);
            abort(403, $error);
        }
    
        try {
            \Log::debug('🔍 Validating request data');
    
            $rules = [
                'ctc' => 'required|numeric|min:0',
                'basic_salary' => [
                    'required', 
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value > $request->ctc) {
                            $fail('Basic salary cannot be greater than CTC.');
                        }
                    }
                ],
                'badges' => 'nullable|array',
                'badges.*.beneficiary_badge_id' => 'required|exists:beneficiary_badges,id',
                'badges.*.is_applicable' => 'sometimes|boolean',
                'badges.*.custom_value' => 'nullable|numeric|min:0',
                'badges.*.custom_calculation_type' => 'nullable|in:flat,percentage',
                'badges.*.custom_based_on' => 'nullable|string',
                'badges.*.start_date' => 'nullable|date',
                'badges.*.end_date' => 'nullable|date|after_or_equal:badges.*.start_date',
            ];
    
            // Ensure basic salary is properly formatted
            $basicSalary = (float) str_replace(',', '', $request->input('basic_salary', 0));
            $ctc = (float) str_replace(',', '', $request->input('ctc', 0));
            
            $request->merge([
                'basic_salary' => $basicSalary,
                'ctc' => $ctc,
                'basic_salary_type' => 'fixed',
                'basic_salary_percent' => null,
                'basic_salary_fixed' => $basicSalary
            ]);
    
            $validatedData = $request->validate($rules);
    
            \Log::debug('✅ Validation passed', ['validated_data' => $validatedData]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
                'employee_id' => $employee->id,
                'user_id' => Auth::id()
            ]);
            throw $e;
        }
    
        DB::beginTransaction();
        \Log::debug('💾 Database transaction started', ['employee_id' => $employee->id]);
    
        try {
            $ctc = (float) $validatedData['ctc'];
            $basicSalary = (float) $validatedData['basic_salary'];
            $companyCurrency = $employee->company->default_currency ?? config('app.currency', 'INR');
            
            // Calculate HRA (50% of basic) and DA (20% of basic)
            $hra = $basicSalary * 0.5;
            $da = $basicSalary * 0.2;
            $otherAllowances = max(0, $ctc - ($basicSalary + $hra + $da));
            
            // Log calculations for debugging
            \Log::debug('💵 Salary calculations', [
                'ctc' => $ctc,
                'basic_salary' => $basicSalary,
                'hra' => $hra,
                'da' => $da,
                'other_allowances' => $otherAllowances
            ]);
    
            // Calculate gross salary as sum of all components
            $grossSalary = $basicSalary + $hra + $da + $otherAllowances;
            
            $salaryData = [
                'ctc' => $ctc,  // Save CTC as provided
                'gross_salary' => $grossSalary,  // Calculate gross from components
                'net_salary' => $grossSalary,  // Net is same as gross before deductions
                'basic_salary' => $basicSalary,
                'hra' => $hra,
                'da' => $da,
                'other_allowances' => $otherAllowances,
                'status' => 'active',
                'currency' => $companyCurrency,
                'payment_frequency' => 'monthly',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'effective_from' => now(),
                'is_current' => true,
                'updated_at' => now()  // Explicitly set updated_at
            ];

            // Only include created_by if the column exists
            if (Schema::hasColumn('employee_salaries', 'created_by')) {
                $salaryData['created_by'] = Auth::id();
            }

            $currentSalary = EmployeeSalary::updateOrCreate(
                ['employee_id' => $employee->id, 'is_current' => true],
                $salaryData
            );
    
            \Log::info('💸 Salary record saved or updated', [
                'employee_id' => $employee->id,
                'salary_id' => $currentSalary->id,
                'ctc' => $ctc,
                'basic_salary' => $basicSalary,
                'hra' => $basicSalary * 0.5,
                'da' => $basicSalary * 0.2,
                'other_allowances' => max(0, $ctc - ($basicSalary + ($basicSalary * 0.5) + ($basicSalary * 0.2)))
            ]);
    
            // Ensure only one current salary exists
            EmployeeSalary::where('employee_id', $employee->id)
                ->where('id', '!=', $currentSalary->id)
                ->where('is_current', true)
                ->update(['is_current' => false]);
    
            $syncData = [];
            if (!empty($validatedData['badges'])) {
                \Log::debug('🏷️ Processing beneficiary badges', [
                    'badges_count' => count($validatedData['badges']),
                    'badges_data' => $validatedData['badges']
                ]);
    
                foreach ($validatedData['badges'] as $index => $badge) {
                    if (!isset($badge['beneficiary_badge_id'])) {
                        \Log::warning('⚠️ Invalid badge format', [
                            'index' => $index,
                            'badge_data' => $badge
                        ]);
                        continue;
                    }
    
                    $isApplicable = isset($badge['is_applicable']) && filter_var($badge['is_applicable'], FILTER_VALIDATE_BOOLEAN);
                    $hasCustomValue = isset($badge['custom_value']) && $badge['custom_value'] !== null;
    
                    if (!$isApplicable && !$hasCustomValue) {
                        continue;
                    }
    
                    $syncData[$badge['beneficiary_badge_id']] = [
                        'is_applicable' => $isApplicable,
                        'custom_value' => $badge['custom_value'] ?? null,
                        'custom_calculation_type' => $badge['custom_calculation_type'] ?? null,
                        'custom_based_on' => $badge['custom_calculation_type'] === 'percentage'
                            ? ($badge['custom_based_on'] ?? null)
                            : null,
                        'start_date' => $badge['start_date'] ?? null,
                        'end_date' => $badge['end_date'] ?? null,
                    ];
    
                    \Log::debug('🧷 Badge sync prepared', [
                        'badge_id' => $badge['beneficiary_badge_id'],
                        'sync_data' => $syncData[$badge['beneficiary_badge_id']]
                    ]);
                }
            }
    
            $employee->beneficiaryBadges()->sync($syncData);
            \Log::info('✅ Beneficiary badges synced', [
                'employee_id' => $employee->id,
                'badges_synced' => count($syncData)
            ]);
    
            DB::commit();
            \Log::info('🎉 Payroll configuration updated successfully', [
                'employee_id' => $employee->id
            ]);
    
            return redirect()
                ->route('admin.payroll.employee-configurations.edit', $employee->id)
                ->with('success', 'Employee payroll configuration updated successfully.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('🔥 Failed to update payroll configuration', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return redirect()
                ->back()
                ->with('error', 'Failed to update employee payroll configuration: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Set a salary as the current active salary for an employee
     *
     * @param Employee $employee
     * @param EmployeeSalary|null $employeeSalary
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function setCurrent(Employee $employee, EmployeeSalary $employeeSalary = null)
    {
        try {
            $companyId = Auth::user()->company_id;
            
            // Verify the employee belongs to the company
            if ($employee->company_id !== $companyId) {
                throw new \Exception('Unauthorized action. Employee does not belong to your company.');
            }
            
            // If no salary is provided, check if we're creating a new one
            if (!$employeeSalary) {
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No salary record found. Please create a salary record first.',
                        'redirect' => route('admin.payroll.employee-configurations.edit', $employee->id)
                    ], 404);
                }
                
                return redirect()
                    ->route('admin.payroll.employee-configurations.edit', $employee->id)
                    ->with('error', 'No salary record found. Please create a salary record first.');
            }
            
            // Verify the salary record belongs to the employee
            if ($employeeSalary->employee_id !== $employee->id) {
                throw new \Exception('The specified salary does not belong to this employee.');
            }

            // Get the current state from the request
            $isCurrent = request()->has('is_current') ? (bool)request('is_current') : true;

            DB::beginTransaction();

            if ($isCurrent) {
                // If setting as current, update all other records
                EmployeeSalary::where('employee_id', $employee->id)
                    ->where('id', '!=', $employeeSalary->id)
                    ->update(['is_current' => false]);
                
                // Update the current salary status
                $employeeSalary->update(['is_current' => true]);
            } else {
                // If setting as inactive, just update this record
                $employeeSalary->update(['is_current' => false]);
            }

            DB::commit();

            $message = $isCurrent 
                ? 'Salary has been set as current successfully.'
                : 'Salary has been set as inactive.';

            // Return JSON response for AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'is_current' => $isCurrent,
                    'salary' => $employeeSalary->fresh()
                ]);
            }

            return redirect()
                ->route('admin.payroll.employee-configurations.edit', [
                    'employee' => $employee->id,
                    'salary_id' => $employeeSalary->id
                ])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update salary status', [
                'employee_id' => $employee->id,
                'salary_id' => $employeeSalary->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Failed to update salary status: ' . $e->getMessage();
            
            // Return JSON response for AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', $errorMessage);
        }
    }
    
    /**
     * Create a new salary record for an employee
     *
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    /**
     * Update the employee's salary via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSalary(Request $request, Employee $employee)
    {
        \Log::info('🚀 Starting salary update', [
            'employee_id' => $employee->id,
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $request->all()
        ]);

        $companyId = Auth::user()->company_id;
        
        if ($employee->company_id !== $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. Employee does not belong to your company.'
            ], 403);
        }

        try {
            $validatedData = $request->validate([
                'ctc' => 'required|numeric|min:0',
                'basic_salary' => [
                    'required',
                    'numeric',
                    'min:0',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value > $request->ctc) {
                            $fail('Basic salary cannot be greater than CTC.');
                        }
                    }
                ]
            ]);

            // Ensure values are properly formatted as floats
            $ctc = (float) $validatedData['ctc'];
            $basicSalary = (float) $validatedData['basic_salary'];
            $companyCurrency = $employee->company->default_currency ?? config('app.currency', 'INR');
            
            // Calculate HRA (50% of basic) and DA (20% of basic)
            $hra = $basicSalary * 0.5;
            $da = $basicSalary * 0.2;
            $otherAllowances = max(0, $ctc - ($basicSalary + $hra + $da));
            
            // Calculate gross salary as sum of all components
            $grossSalary = $basicSalary + $hra + $da + $otherAllowances;
            
            // Create or update the salary record
            $currentSalary = $employee->currentSalary ?? new EmployeeSalary();
            
            $salaryData = [
                'employee_id' => $employee->id,
                'ctc' => $ctc,
                'gross_salary' => $grossSalary,
                'net_salary' => $grossSalary,  // Net is same as gross before deductions
                'basic_salary' => $basicSalary,
                'hra' => $hra,
                'da' => $da,
                'other_allowances' => $otherAllowances,
                'status' => 'active',
                'currency' => $companyCurrency,
                'payment_frequency' => 'monthly',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'effective_from' => now(),
                'is_current' => true,
            ];
            
            // If this is a new salary record or the values have changed significantly
            if (!$currentSalary->exists || 
                abs($currentSalary->ctc - $ctc) > 0.01 || 
                abs($currentSalary->basic_salary - $basicSalary) > 0.01) {
                
                // Mark current salary as not current if it exists
                if ($currentSalary->exists) {
                    $currentSalary->update(['is_current' => false]);
                }
                
                // Create new salary record
                $employee->employeeSalaries()->create($salaryData);
                
                // Clear any cached salary data
                if (method_exists($employee, 'forgetSalaryCache')) {
                    $employee->forgetSalaryCache();
                }
                
                \Log::info('✅ Created new salary record', [
                    'employee_id' => $employee->id,
                    'ctc' => $ctc,
                    'basic_salary' => $basicSalary,
                    'hra' => $hra,
                    'da' => $da,
                    'other_allowances' => $otherAllowances
                ]);
            } else {
                // Just update the existing record
                $currentSalary->update($salaryData);
                \Log::info('✅ Updated existing salary record', [
                    'employee_id' => $employee->id,
                    'salary_id' => $currentSalary->id,
                    'ctc' => $ctc,
                    'basic_salary' => $basicSalary
                ]);
            }
            
            // return response()->json([
            //     'success' => true,
            //     'message' => 'Salary updated successfully',
            //     'ctc' => $ctc,
            //     'basic_salary' => $basicSalary,
            //     'hra' => $hra,
            //     'da' => $da,
            //     'other_allowances' => $otherAllowances
            // ]);
            return redirect()->back()->with('success', 'Salary updated successfully');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Validation failed', [
                'errors' => $e->errors(),
                'employee_id' => $employee->id,
                'user_id' => Auth::id()
            ]);
            
            // return response()->json([
            //     'success' => false,
            //     'message' => 'Validation failed',
            //     'errors' => $e->errors()
            // ], 422);
            return redirect()->back()->with('error', 'Validation failed: ' . $e->getMessage());
            
        } catch (\Exception $e) {
            \Log::error('❌ Error updating salary', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'employee_id' => $employee->id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the salary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new salary record for an employee
     *
     * @param Request $request
     * @param Employee $employee
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function createSalary(Request $request, Employee $employee)
    {
        try {
            $companyId = Auth::user()->company_id;
            
            // Verify the employee belongs to the company
            if ($employee->company_id !== $companyId) {
                throw new \Exception('Unauthorized action. Employee does not belong to your company.');
            }
            
            // Validate the request
            $validated = $request->validate([
                'ctc' => 'required|numeric|min:0',
                'basic_salary' => 'required|numeric|min:0|max:' . $request->ctc,
                'effective_from' => 'required|date',
                'payment_frequency' => 'required|in:monthly,biweekly,weekly',
                'bank_name' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:50',
                'ifsc_code' => 'nullable|string|max:20',
                'notes' => 'nullable|string',
            ]);
            
            DB::beginTransaction();
            
            // Calculate salary components
            $ctc = (float) $validated['ctc'];
            $basicSalary = (float) $validated['basic_salary'];
            $hra = $basicSalary * 0.4; // 40% of basic
            $da = $basicSalary * 0.2;  // 20% of basic
            $otherAllowances = max(0, $ctc - ($basicSalary + $hra + $da));
            
            // Create new salary record
            $salary = new EmployeeSalary([
                'employee_id' => $employee->id,
                'ctc' => $ctc,
                'basic_salary' => $basicSalary,
                'hra' => $hra,
                'da' => $da,
                'other_allowances' => $otherAllowances,
                'gross_salary' => $ctc,
                'net_salary' => $ctc,
                'payment_frequency' => $validated['payment_frequency'],
                'effective_from' => $validated['effective_from'],
                'bank_name' => $validated['bank_name'] ?? null,
                'account_number' => $validated['account_number'] ?? null,
                'ifsc_code' => $validated['ifsc_code'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'active',
                'is_current' => true,
                'created_by' => Auth::id(),
            ]);
            
            // Set all other salaries as not current
            EmployeeSalary::where('employee_id', $employee->id)
                ->update(['is_current' => false]);
                
            $salary->save();
            
            DB::commit();
            
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Salary record created successfully.',
                    'salary' => $salary->fresh()
                ]);
            }
            
            return redirect()
                ->route('admin.payroll.employee-configurations.edit', [
                    'employee' => $employee->id,
                    'salary_id' => $salary->id
                ])
                ->with('success', 'Salary record created successfully.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create salary record', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Failed to create salary record: ' . $e->getMessage();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }
}
