<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\ModuleAccess;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\EmployeeIdPrefix;

class CompanyAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:company_admin');
    }

    /**
     * Save employee ID prefix settings.
     */
    public function saveEmployeeIdPrefix(Request $request)
    {
        $user = $request->user();
        $company = $user->employee->company;

        $validated = $request->validate([
            'prefix' => 'required|string|max:255',
            'padding' => 'required|integer|min:0',
            'start' => 'required|integer|min:0',
            'employment_type' => 'nullable|string|in:permanent,trainee',
        ]);

        $employmentType = $validated['employment_type'] ?? '';

        if ($employmentType === '') {
            // Save two entries for permanent and trainee
            EmployeeIdPrefix::updateOrCreate(
                ['company_id' => $company->id, 'employment_type' => 'permanent'],
                [
                    'prefix' => $validated['prefix'],
                    'padding' => $validated['padding'],
                    'start' => $validated['start'],
                ]
            );
            EmployeeIdPrefix::updateOrCreate(
                ['company_id' => $company->id, 'employment_type' => 'trainee'],
                [
                    'prefix' => $validated['prefix'],
                    'padding' => $validated['padding'],
                    'start' => $validated['start'],
                ]
            );
        } else {
            // Save single entry for specified employment type
            EmployeeIdPrefix::updateOrCreate(
                ['company_id' => $company->id, 'employment_type' => $employmentType],
                [
                    'prefix' => $validated['prefix'],
                    'padding' => $validated['padding'],
                    'start' => $validated['start'],
                ]
            );
        }

        return redirect()->back()->with('success', 'Employee ID prefix settings saved successfully.');
    }

    /**
     * Display the company admin dashboard.
     */
    // public function dashboard()
    // {
    //     $user = Auth::user();
        
    //     try {
    //         // Check if user has an employee record
    //         if (!$user->employee) {
    //             // Get the first company if company_id is not set
    //             $companyId = $user->company_id ?? Company::first()?->id;
                
    //             if (!$companyId) {
    //                 // If no company exists, create a default one
    //                 // $company = Company::create([
    //                 //     'name' => $user->name . "'s Company",
    //                 //     'email' => $user->email,
    //                 //     'phone' => '',
    //                 //     'website' => '',
    //                 //     'address' => '',
    //                 //     'status' => 'active',
    //                 //     'created_by' => $user->id,
    //                 // ]);
    //                 // $companyId = $company->id;
    //                 // $user->company_id = $companyId;
    //                 // $user->save();
    //                 Log::info('No company found for this user');
    //             }

    //             // Create a basic employee record
    //             $employee = Employee::create([
    //                 'user_id' => $user->id,
    //                 'company_id' => $companyId,
    //                 'name' => $user->name,
    //                 'email' => $user->email,
    //                 'department_id' => 1, // Default department
    //                 'designation_id' => 1, // Default designation
    //                 'gender' => 'other',
    //                 'employment_type' => 'full_time',
    //                 'joining_date' => now(),
    //                 'phone' => '',
    //                 'address' => '',
    //                 'emergency_contact' => '',
    //                 'status' => 'active',
    //                 'created_by' => $user->id,
    //             ]);

    //             // Refresh the user's employee relationship
    //             $user = \App\Models\User::find($user->id);
    //             $user->load('employee');
    //         }

    //         // Make sure we have a valid user
    //         if (!$user) {
    //             throw new \Exception('User not found');
    //         }

    //         // Get the company, either from employee or directly from user
    //         $company = $user->company;
            
    //         if (!$company) {
    //             throw new \Exception('No company found for this user');
    //         }

    //         return view('company-admin.dashboard.index', compact('company'));
            
    //     } catch (\Exception $e) {
    //         Log::error('Error in CompanyAdminController@dashboard: ' . $e->getMessage());
    //         return redirect()->route('home')->with('error', 'Failed to load dashboard: ' . $e->getMessage());
    //     }
    // }

    public function dashboard()
{
    $user = Auth::user();
    
    try {
        // Check if user has an employee record
        if (!$user->employee) {
            // Get the first company if company_id is not set
            $companyId = $user->company_id ?? Company::first()?->id;
            
            if (!$companyId) {
                Log::info('No company found for this user');
            }

            // Create a basic employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'company_id' => $companyId,
                'name' => $user->name,
                'email' => $user->email,
                'department_id' => Department::first()?->id ?? 1,
                'designation_id' => Designation::first()?->id ?? 1,
                'gender' => 'other',
                'employment_type' => 'full_time',
                'joining_date' => now(),
                'phone' => '',
                'address' => '',
                'emergency_contact' => '',
                'status' => 'active',
                'created_by' => $user->id,
            ]);

            // Refresh the user's employee relationship
            $user = User::find($user->id);
            $user->load('employee');
        }

        // Get the company
        $company = $user->company;
        
        if (!$company) {
            throw new \Exception('No company found for this user');
        }

        // Get total employees
        $totalEmployees = User::where('company_id', $company->id)->count();

        // Get today's attendance count
        $todayAttendanceCount = DB::table('attendances as a')
            ->join('employees as e', 'a.employee_id', '=', 'e.id')
            ->whereDate('a.created_at', now()->format('Y-m-d'))
            ->where('e.company_id', $company->id)
            ->count();

        // Get employees on leave
        $onLeaveCount = \App\Models\LeaveRequest::whereHas('employee', function($q) use ($company) {
                $q->where('company_id', $company->id);
            })
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->where('status', 'approved')
            ->count();

        // Get department count
        $departmentCount = Department::where('company_id', $company->id)->count();

        // Get employee distribution by role
        $roles = User::where('company_id', $company->id)
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role');

        $companyRoleLabels = $roles->keys()->toArray();
        $companyRoleData = $roles->values()->toArray();

        return view('company_admin.dashboard', compact(
            'totalEmployees',
            'todayAttendanceCount',
            'onLeaveCount',
            'departmentCount',
            'companyRoleLabels',
            'companyRoleData'
        ));
        
    } catch (\Exception $e) {
        Log::error('Error in CompanyAdminController@dashboard: ' . $e->getMessage());
        return redirect()->route('home')->with('error', 'Failed to load dashboard: ' . $e->getMessage());
    }
}

    /**
     * Display module access management page.
     */
    public function moduleAccess()
    {
        $user = Auth::user();
        $company = $user->employee->company;
        
        // Get current module access settings
        $modules = ModuleAccess::where('company_id', $company->id)
            ->get()
            ->groupBy('module_name')
            ->map(function ($moduleGroup) {
                return $moduleGroup->mapWithKeys(function ($access) {
                    return [$access->role => $access->has_access];
                });
            })
            ->toArray();

        return view('company-admin.module-access.index', compact('modules'));
    }

    /**
     * Update module access settings.
     */
    public function updateModuleAccess(Request $request)
    {
        try {
            $user = Auth::user();
            $company = $user->employee->company;

            DB::beginTransaction();

            foreach ($request->input('modules', []) as $moduleName => $roleAccess) {
                foreach ($roleAccess as $role => $hasAccess) {
                    ModuleAccess::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'module_name' => $moduleName,
                            'role' => $role,
                        ],
                        ['has_access' => (bool) $hasAccess]
                    );
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Module access settings updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating module access: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating module access settings.');
        }
    }

    /**
     * List employees for the company.
     */
    public function employees()
    {
        $user = Auth::user();
        $company = $user->employee->company;
        
        $employees = Employee::with(['user', 'department'])
            ->where('company_id', $company->id)
            ->paginate(10);

        return view('company-admin.employees.index', compact('employees'));
    }

    /**
     * Update employee role.
     */
    public function updateEmployeeRole(Request $request, Employee $employee)
    {
        try {
            $user = Auth::user();
            $company = $user->employee->company;

            // Ensure employee belongs to the same company
            if ($employee->company_id !== $company->id) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            $request->validate([
                'role' => 'required|in:admin,employee,reporter'
            ]);

            DB::beginTransaction();

            // Load the user relationship if not already loaded
            $employee->load('user');
            
            // Check if user exists
            if (!$employee->user) {
                throw new \Exception('User record not found for this employee.');
            }

            // Prevent changing company_admin role
            if ($employee->user->role === 'company_admin' || $request->role === 'company_admin') {
                return redirect()->back()->with('error', 'Changing the company_admin role is not allowed.');
            }

            // Update user role
            $employee->user->role = $request->role;
            $employee->user->save();

            DB::commit();
            return redirect()->back()->with('success', 'Employee role updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating employee role: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new employee.
     */
    public function createEmployee()
    {
        $user = Auth::user();
        $company = $user->employee->company;
        
        $departments = \App\Models\Department::where('company_id', $company->id)->get();
        $designations = \App\Models\Designation::where('company_id', $company->id)->get();
        $managers = \App\Models\Employee::where('company_id', $company->id)->get();
        
        return view('company-admin.employees.create', compact('company', 'departments', 'designations', 'managers'));
    }

    private function generateEmployeeCode($company, $employmentType = null)
    {
        // If employment type is not provided, use default format
        if (!$employmentType) {
            $prefix = '#' . strtoupper(substr($company->name, 0, 3)) . '000';
            $lastEmployee = Employee::where('company_id', $company->id)
                ->whereNotNull('employee_code')
                ->where('employee_code', 'like', $prefix.'%')
                ->orderBy('id', 'desc')
                ->first();

            $nextNumber = 1;
            if ($lastEmployee) {
                $numericPart = (int) substr($lastEmployee->employee_code, -3);
                $nextNumber = $numericPart + 1;
            }
            return substr($prefix, 0, -3) . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        }

        // Get the prefix settings for the company
        $prefixSettings = EmployeeIdPrefix::where('company_id', $company->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // If no prefix settings found, use default
        if ($prefixSettings->isEmpty()) {
            return '#' . strtoupper(substr($company->name, 0, 3)) . str_pad('1', 3, '0', STR_PAD_LEFT);
        }

        // Check if we have a common prefix (both types have same settings)
        if ($prefixSettings->count() == 2) {
            $permanent = $prefixSettings->where('employment_type', 'permanent')->first();
            $trainee = $prefixSettings->where('employment_type', 'trainee')->first();

            if ($permanent->prefix === $trainee->prefix && 
                $permanent->padding === $trainee->padding && 
                $permanent->start === $trainee->start) {
                // Use common settings
                $prefixSetting = $permanent;
            } else {
                // Use type-specific settings
                $prefixSetting = $prefixSettings->where('employment_type', $employmentType)->first();
            }
        } else {
            // Only one type exists, check if it matches the employee type
            $prefixSetting = $prefixSettings->first();
            if ($prefixSetting->employment_type !== $employmentType && $prefixSettings->count() == 1) {
                // If settings don't exist for this employment type, use default
                return '#' . strtoupper(substr($company->name, 0, 3)) . str_pad('1', 3, '0', STR_PAD_LEFT);
            }
        }

        // Get the last employee number for this prefix
        $lastEmployee = Employee::where('company_id', $company->id)
            ->where('employee_code', 'LIKE', $prefixSetting->prefix . '%')
            ->orderBy('created_at', 'desc')
            ->first();

        $nextNumber = $prefixSetting->start;
        if ($lastEmployee) {
            // Extract the number from the last employee code
            $lastNumber = intval(substr($lastEmployee->employee_code, strlen($prefixSetting->prefix)));
            $nextNumber = $lastNumber + 1;
        }

        // Format the number according to padding settings
        $formattedNumber = str_pad($nextNumber, $prefixSetting->padding, '0', STR_PAD_LEFT);
        
        return $prefixSetting->prefix . $formattedNumber;
    }

    public function storeEmployee(Request $request)
    {
        \Log::info('CompanyAdminController@storeEmployee called');
        $user = Auth::user();
        $company = $user->employee->company;

        // Validate the request
        $validated = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'parent_name' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'contact_number' => 'required|string|max:20',
            'personal_email' => 'required|email',
            'official_email' => 'nullable|email',
            'current_address' => 'required|string',
            'permanent_address' => 'required|string',
            // Job Details
            'employee_code' => 'nullable|string', // readonly, generated
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'employment_type' => 'required|in:permanent,trainee',
            'joining_date' => 'required|date',
            'location' => 'required|string',
            'probation_period' => 'nullable|integer',
            'reporting_manager' => 'required|exists:employees,id',
            // Salary Details
            'ctc' => 'required|numeric|min:0',
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'required|string',
            'account_number' => 'required|string',
            'ifsc_code' => 'required|string',
            'pan_number' => 'required|string',
            // Other Details
            'emergency_contact' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'blood_group' => 'nullable|string',
            'nominee_details' => 'nullable|string',
            // Documents
            'aadhaar_card.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'pan_card.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'passport_photo.*' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'resume.*' => 'nullable|file|mimes:pdf|max:2048',
            'qualification_certificate.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'experience_letters.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'relieving_letter.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'offer_letter.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'bank_passbook.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'signed_offer_letter.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);
        \Log::info('Employee creation validation passed');

        try {
            DB::beginTransaction();
            \Log::info('Employee created successfully');
            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['personal_email'], // Use personal email for login
                'password' => Hash::make(12345678), // Random password, user will need to reset
                'company_id' => $company->id,
                'status' => 'active',
                'role' => 'employee'
            ]);

            // Create employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'employee_code' => $this->generateEmployeeCode($company, $validated['employment_type']),
                'name' => $validated['name'],
                'parent_name' => $validated['parent_name'],
                'gender' => $validated['gender'],
                'dob' => $validated['dob'],
                'marital_status' => $validated['marital_status'],
                'contact_number' => $validated['contact_number'],
                'email' => $validated['personal_email'],
                'official_email' => $validated['official_email'],
                'phone' => $validated['contact_number'],
                'department_id' => $validated['department_id'],
                'designation_id' => $validated['designation_id'],
                'employment_type' => $validated['employment_type'],
                'joining_date' => $validated['joining_date'],
                'location' => $validated['location'],
                'probation_period' => $validated['probation_period'],
                'reporting_manager_id' => $validated['reporting_manager'],
                'current_address' => $validated['current_address'],
                'permanent_address' => $validated['permanent_address'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_contact_relation' => $validated['emergency_contact_relation'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'blood_group' => $validated['blood_group'],
                'nominee_details' => $validated['nominee_details'],
                // 'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            // Ensure values are properly formatted as floats
            $ctc = (float) $validated['ctc'];
            $basicSalary = (float) $validated['basic_salary'];
            $companyCurrency = $employee->company->default_currency ?? config('app.currency', 'INR');
            
            // Calculate HRA (50% of basic) and DA (20% of basic)
            $hra = $basicSalary * 0.5;
            $da = $basicSalary * 0.2;
            $otherAllowances = max(0, $ctc - ($basicSalary + $hra + $da));
            
            // Calculate gross salary as sum of all components
            $grossSalary = $basicSalary + $hra + $da + $otherAllowances;
                        
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
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'ifsc_code' => $validated['ifsc_code'],
                'pan_number' => $validated['pan_number'],
            ];

            // Create salary record
            \App\Models\EmployeeSalary::create($salaryData);

            // \App\Models\EmployeeSalary::create([
            //     'employee_id' => $employee->id,
            //     'ctc' => $validated['ctc'],
            //     'basic_salary' => $validated['basic_salary'],
            //     'bank_name' => $validated['bank_name'],
            //     'account_number' => $validated['account_number'],
            //     'ifsc_code' => $validated['ifsc_code'],
            //     'pan_number' => $validated['pan_number'],
            //     // 'created_by' => Auth::id(),
            // ]);

            // Handle document uploads
            $documentTypes = [
                'aadhaar_card', 'pan_card', 'passport_photo', 'resume',
                'qualification_certificate', 'experience_letters', 'relieving_letter',
                'offer_letter', 'bank_passbook', 'signed_offer_letter'
            ];

            foreach ($documentTypes as $docType) {
                $paths = [];
                if ($request->hasFile($docType)) {
                    foreach ($request->file($docType) as $file) {
                        $paths[] = $file->store('employee_documents/' . $employee->id, 'public');
                    }
                }
                if (count($paths)) {
                    \App\Models\EmployeeDocument::create([
                        'company_id' => $company->id,
                        'employee_id' => $employee->id,
                        'type' => $docType,
                        'file_path' => json_encode($paths),
                    ]);
                }
            }

            DB::commit();

            // Send welcome email with password reset link
            // TODO: Implement welcome email

            return redirect()->route('company-admin.employees.index')
                ->with('success', 'Employee created successfully.');

        } catch (\Exception $e) {
            \Log::error('Error in CompanyAdminController@storeEmployee: ' . $e->getMessage());
            DB::rollBack();
            Log::error('Error in CompanyAdminController@storeEmployee: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display company settings.
     */
    public function settings()
    {
        $user = Auth::user();
        $company = $user->employee->company;

        return view('company-admin.settings.index', compact('company'));
    }

    /**
     * Update company settings.
     */
    public function updateSettings(Request $request)
    {
        try {
            $user = Auth::user();
            $company = $user->employee->company;

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'website' => 'required|url',
                'address' => 'required|string'
            ]);

            $company->update($request->all());

            return redirect()->back()->with('success', 'Company settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating company settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating company settings.');
        }
    }

    /**
     * Get employee ID prefix settings.
     */
    public function getEmployeeIdPrefix(Request $request)
    {
        $user = $request->user();
        $company = $user->employee->company;

        $prefixes = EmployeeIdPrefix::where('company_id', $company->id)->get();
        
        if ($prefixes->isEmpty()) {
            return response()->json([
                'status' => 'empty',
                'data' => null
            ]);
        }

        // Check if both employment types have the same settings
        if ($prefixes->count() == 2) {
            $permanent = $prefixes->where('employment_type', 'permanent')->first();
            $trainee = $prefixes->where('employment_type', 'trainee')->first();

            if ($permanent->prefix === $trainee->prefix && 
                $permanent->padding === $trainee->padding && 
                $permanent->start === $trainee->start) {
                return response()->json([
                    'status' => 'common',
                    'data' => $permanent
                ]);
            }
        }

        return response()->json([
            'status' => 'specific',
            'data' => $prefixes->keyBy('employment_type')
        ]);
    }
}
