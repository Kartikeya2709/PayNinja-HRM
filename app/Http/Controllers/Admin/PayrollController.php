<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Employee;
use App\Models\Company;
use App\Services\PayrollService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    protected PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
        // Apply middleware for authorization if needed, e.g.,
        // $this->middleware('can:create_payroll')->only(['create', 'store']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Payroll::class);

        $company = Auth::user()->company; // Assuming admin is associated with a company
        if (!$company) {
            // Or handle as a global admin if your system supports it
            // For now, redirect or show an error if no company context
            return redirect()->route('admin.dashboard')->with('error', 'No company context found.');
        }

        $payrolls = Payroll::where('company_id', $company->id)
            ->with(['employee.user', 'processedBy']) // Eager load relationships
            ->latest('pay_period_end') // Order by most recent pay period end
            ->paginate(15);

        return view('admin.payroll.index', compact('payrolls', 'company'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $company = Auth::user()->company;
        $companyId = $company?->id;
        if (!$companyId && !Auth::user()->hasRole('superadmin')) {
            return redirect()->route('home')->with('error', 'Company context not found. Please ensure your user is associated with a company.');
        }

        // Superadmin might need a company selector first, or this defaults to a specific context.
        // For now, assume company_id is available or they operate on all if superadmin (though payroll is company-specific).
        // This part might need refinement based on superadmin payroll generation flow.
        // For a typical admin, they generate for their own company.

        $employees = collect();
        if ($companyId) {
            $employees = Employee::where('company_id', $companyId)
                                // ->where('status', 'active') // Only active employees
                                ->orderBy('name')->get();
        } else if (Auth::user()->hasRole('superadmin')) {
            // For superadmin, list all active employees. They might need to select a company on the form.
            // Or, the form should have a company selector that then populates employees.
            // For simplicity, let's allow selecting any active employee. The service will use employee's company.
            $employees = Employee::where('status', 'active')->orderBy('name')->get();
        }

        if ($employees->isEmpty() && !$companyId && !Auth::user()->hasRole('superadmin')) {
             return redirect()->route('admin.payroll.index')->with('info', 'No active employees found in your company to generate payroll for.');
        }
        // If superadmin and no employees at all, that's a different state.

        return view('admin.payroll.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'payroll_type' => 'required|in:single,bulk',
                'month' => 'required|date_format:Y-m',
                'employee_id' => 'required_if:payroll_type,single|exists:employees,id',
                'skip_processed' => 'sometimes|boolean',
            ]);

            $company = auth()->user()->company;
            if (!$company && !auth()->user()->hasRole('superadmin')) {
                return redirect()->route('home')->with('error', 'Company context not found.');
            }

            // For single employee payroll
            if ($request->payroll_type === 'single') {
                $employee = Employee::with('company')->findOrFail($request->employee_id);

                // Authorization check
                if (!auth()->user()->hasRole('superadmin') && $company->id !== $employee->company_id) {
                    return back()->with('error', 'You are not authorized to generate payroll for this employee.');
                }

                // Derive pay period start and end from month
                $month = Carbon::createFromFormat('Y-m', $request->month);
                $payPeriodStart = $month->copy()->startOfMonth();
                $payPeriodEnd = $month->copy()->endOfMonth();

                $payroll = $this->payrollService->generatePayrollForEmployee(
                    $employee,
                    $payPeriodStart,
                    $payPeriodEnd,
                    $employee->company ?? $company
                );

                return redirect()->route('admin.payroll.show', $payroll->id)
                    ->with('success', 'Payroll generated successfully for ' . $employee->name);
            }
            // For bulk payroll generation
            else if ($request->payroll_type === 'bulk') {
                $skipProcessed = $request->boolean('skip_processed', true);
                // Derive pay period start and end from month
                $month = Carbon::createFromFormat('Y-m', $request->month);
                $payPeriodStart = $month->copy()->startOfMonth();
                $payPeriodEnd = $month->copy()->endOfMonth();

                // Get all active employees for the company
                // $query = Employee::with('company')
                //     // ->where('status', 'active')
                //     ->where('company_id', $company->id);

                $query = Employee::with(['company', 'user'])
                ->where('company_id', $company->id)
                ->whereHas('user', function($q) {
                    $q->where('role', '!=', 'company_admin')
                      ->orWhereNull('role');
                });

                $employees = $query->get();

                if ($employees->isEmpty()) {
                    return back()->with('error', 'No active employees found to process payroll.');
                }

                $processed = 0;
                $skipped = 0;
                $errors = [];

                foreach ($employees as $employee) {
                    try {
                        // Skip if payroll already exists for this period and skip_processed is true
                        if ($skipProcessed) {
                            $existingPayroll = Payroll::where('employee_id', $employee->id)
                                ->where('pay_period_start', $payPeriodStart->toDateString())
                                ->where('pay_period_end', $payPeriodEnd->toDateString())
                                ->where('status', '!=', 'cancelled')
                                ->exists();

                            if ($existingPayroll) {
                                $skipped++;
                                continue;
                            }
                        }

                        $this->payrollService->generatePayrollForEmployee(
                            $employee,
                            $payPeriodStart,
                            $payPeriodEnd,
                            $employee->company ?? $company
                        );
                        $processed++;
                    } catch (\Exception $e) {
                        Log::error('Error generating payroll for employee ' . $employee->id . ': ' . $e->getMessage());
                        $errors[] = 'Employee ' . ($employee->name ?? 'Unknown') . ': ' . $e->getMessage();
                    }
                }

                $message = "Payroll generation completed. Processed: {$processed}, Skipped: {$skipped}";
                if (!empty($errors)) {
                    $message .= ". " . count($errors) . " errors occurred.";
                    return back()
                        ->with('warning', $message)
                        ->with('error_details', $errors);
                }

                return redirect()->route('admin.payroll.index')
                    ->with('success', $message);
            }

            // This should never be reached due to the payroll_type validation
            return back()->with('error', 'Invalid payroll type.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Payroll Generation Error: ' . $e->getMessage(), [
                'employee_id' => $request->employee_id,
                'month' => $request->month,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Payroll generation failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payroll $payroll)
    {
        $this->authorize('view', $payroll);

        $payroll->load([
            'items',
            'employee.user',
            'employee.designation',
            'employee.department',
            'processedBy', 
            'company'
        ]);

        return view('admin.payroll.show', compact('payroll'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payroll $payroll)
    {
        $this->authorize('update', $payroll);

        if ($payroll->status === 'paid') {
            return redirect()->route('admin.payroll.index')->with('error', 'Cannot edit a paid payroll record.');
        }

        $payroll->load([
            'items',
            'employee.user',
            'employee.designation',
            'employee.department',
            'processedBy',
            'company'
        ]);

        return view('admin.payroll.edit', compact('payroll'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payroll $payroll)
    {
        $this->authorize('update', $payroll);

        if ($payroll->status === 'paid') {
            return redirect()->route('admin.payroll.index')->with('error', 'Cannot update a paid payroll record.');
        }

        $request->validate([
            'items' => 'required|array',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.type' => 'required|in:earning,deduction,reimbursement',
            'items.*.is_taxable' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            // Update payroll items
            $payroll->items()->delete(); // Remove existing items

            $totalEarnings = 0;
            $totalDeductions = 0;

            foreach ($request->items as $itemData) {
                $payroll->items()->create([
                    'description' => $itemData['description'],
                    'amount' => $itemData['amount'],
                    'type' => $itemData['type'],
                    'is_taxable' => $itemData['is_taxable'] ?? false,
                    'meta' => []
                ]);

                if (in_array($itemData['type'], ['earning', 'reimbursement'])) {
                    $totalEarnings += $itemData['amount'];
                } elseif ($itemData['type'] === 'deduction') {
                    $totalDeductions += $itemData['amount'];
                }
            }

            // Update payroll totals
            $payroll->gross_salary = $totalEarnings;
            $payroll->total_deductions = $totalDeductions;
            $payroll->net_salary = max(0, $totalEarnings - $totalDeductions);
            $payroll->notes = $request->notes;

            $payroll->save();

            DB::commit();

            return redirect()->route('admin.payroll.show', $payroll->id)
                ->with('success', 'Payroll updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error updating payroll {$payroll->id}: " . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update payroll: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payroll $payroll)
    {
        $this->authorize('delete', $payroll);

        if ($payroll->status === 'paid') {
            return redirect()->route('admin.payroll.index')->with('error', 'Cannot delete a paid payroll record. Please cancel it first if necessary.');
        }

        try {
            $payroll->items()->delete(); // Delete associated payroll items
            $payroll->delete(); // Soft delete or hard delete based on model setup
            return redirect()->route('admin.payroll.index')->with('success', 'Payroll record deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting payroll record {$payroll->id}: " . $e->getMessage());
            return redirect()->route('admin.payroll.index')->with('error', 'Failed to delete payroll record.');
        }
    }

    /**
     * Mark the specified payroll as paid.
     */
    public function markAsPaid(Payroll $payroll)
    {
        $this->authorize('update', $payroll); // Or a more specific permission like 'markAsPaid'

        if (!in_array($payroll->status, ['pending', 'processed', 'generated'])) {
            return redirect()->route('admin.payroll.index')->with('error', "Payroll record is already {$payroll->status} and cannot be marked as paid.");
        }

        try {
            $payroll->status = 'paid';
            $payroll->payment_date = now();
            $payroll->save();
            $employeeName = $payroll->employee?->user?->name ?? 'Unknown Employee';
            return redirect()->route('admin.payroll.index')->with('success', "Payroll for {$employeeName} marked as paid.");
        } catch (\Exception $e) {
            Log::error("Error marking payroll {$payroll->id} as paid: " . $e->getMessage());
            return redirect()->route('admin.payroll.index')->with('error', 'Failed to mark payroll as paid.');
        }
    }

    /**
     * Cancel the specified payroll.
     */
    public function cancel(Payroll $payroll)
    {
        $this->authorize('update', $payroll); // Or a more specific permission like 'cancelPayroll'

        if ($payroll->status === 'paid') {
            return redirect()->route('admin.payroll.index')->with('error', 'Cannot cancel a paid payroll record.');
        }
        if ($payroll->status === 'cancelled') {
            return redirect()->route('admin.payroll.index')->with('info', 'Payroll record is already cancelled.');
        }

        try {
            $payroll->status = 'cancelled';
            $payroll->save();
            $employeeName = $payroll->employee?->user?->name ?? 'Unknown Employee';
            return redirect()->route('admin.payroll.index')->with('success', "Payroll for {$employeeName} has been cancelled.");
        } catch (\Exception $e) {
            Log::error("Error cancelling payroll {$payroll->id}: " . $e->getMessage());
            return redirect()->route('admin.payroll.index')->with('error', 'Failed to cancel payroll.');
        }
    }

    /**
     * Bulk approve and mark multiple payrolls as paid.
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'payroll_ids' => 'required|array',
            'payroll_ids.*' => 'exists:payrolls,id'
        ]);

        $payrollIds = $request->payroll_ids;
        $processed = 0;
        $errors = [];

        foreach ($payrollIds as $payrollId) {
            try {
                $payroll = Payroll::findOrFail($payrollId);

                $this->authorize('update', $payroll);

                if (!in_array($payroll->status, ['pending', 'processed'])) {
                    $employeeName = $payroll->employee?->user?->name ?? 'Unknown Employee';
                    $errors[] = "Payroll #{$payrollId} for {$employeeName} is already {$payroll->status}.";
                    continue;
                }

                $payroll->status = 'paid';
                $payroll->payment_date = now();
                $payroll->save();

                $processed++;
            } catch (\Exception $e) {
                Log::error("Error bulk approving payroll {$payrollId}: " . $e->getMessage());
                $errors[] = "Failed to approve payroll #{$payrollId}.";
            }
        }

        $message = "Bulk approval completed. {$processed} payroll(s) approved successfully.";
        if (!empty($errors)) {
            $message .= " " . count($errors) . " error(s) occurred.";
            return redirect()->route('admin.payroll.index')->with('warning', $message)->with('bulk_errors', $errors);
        }

        return redirect()->route('admin.payroll.index')->with('success', $message);
    }
}
