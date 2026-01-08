<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class LeaveBalanceController extends Controller
{
    /**
     * Get encrypted ID from model ID
     */
    private function getEncryptedId($id)
    {
        return Crypt::encrypt($id);
    }

    /**
     * Get decrypted ID from encrypted string
     */
    private function getDecryptedId($encryptedId)
    {
        try {
            return Crypt::decrypt($encryptedId);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Get model from encrypted ID
     */
    private function getLeaveBalanceFromEncryptedId(string $encryptedId): LeaveBalance
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            return LeaveBalance::findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
    }
    /**
     * Display a listing of the leave balances.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $currentYear = Carbon::now()->year;

        // Get active departments for filtering
        $departments = Department::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        // Get active leave types for filtering
        $leaveTypes = LeaveType::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Build the query for employees with their leave balances
        $query = Employee::where('employees.company_id', $companyId)
            ->join('leave_balances', 'employees.id', '=', 'leave_balances.employee_id')
            ->join('leave_types', 'leave_balances.leave_type_id', '=', 'leave_types.id')
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select([
                'employees.*',
                'departments.name as department_name',
                'leave_types.name as leave_type_name',
                'leave_balances.total_days',
                'leave_balances.used_days',
                'leave_balances.year',
                'leave_balances.id as balance_id'
            ]);

        // Apply filters
        if ($request->filled('department')) {
            $query->where('employees.department_id', $request->department);
        }

        if ($request->filled('leaveType')) {
            $query->where('leave_balances.leave_type_id', $request->leaveType);
        }

        if ($request->filled('year')) {
            $query->where('leave_balances.year', $request->year);
        }

        if ($request->filled('balanceStatus')) {
            if ($request->balanceStatus === 'available') {
                $query->whereRaw('(leave_balances.total_days - leave_balances.used_days) > 0');
            } elseif ($request->balanceStatus === 'exhausted') {
                $query->whereRaw('(leave_balances.total_days - leave_balances.used_days) <= 0');
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('employees.name', 'like', $searchTerm)
                  ->orWhere('employees.email', 'like', $searchTerm)
                  ->orWhere('departments.name', 'like', $searchTerm)
                  ->orWhere('leave_types.name', 'like', $searchTerm);
            });
        }

        // Export functionality
        if ($request->filled('export')) {
            $data = $query->get();
            $fileName = 'leave_balances_' . date('Y-m-d') . '.' . $request->export;

            $headers = [
                'Employee Name',
                'Department',
                'Leave Type',
                'Total Days',
                'Used Days',
                'Remaining Days',
                'Year'
            ];

            $rows = $data->map(function($item) {
                return [
                    $item->name,
                    $item->department_name ?? '-',
                    $item->leave_type_name,
                    $item->total_days,
                    $item->used_days,
                    $item->total_days - $item->used_days,
                    $item->year
                ];
            });

            if ($request->export === 'excel') {
                return response()->streamDownload(function() use ($headers, $rows) {
                    $output = fopen('php://output', 'w');
                    fputcsv($output, $headers);
                    foreach ($rows as $row) {
                        fputcsv($output, $row);
                    }
                    fclose($output);
                }, $fileName, [
                    'Content-Type' => 'text/csv',
                ]);
            } else {
                // Generate PDF
                return Pdf::loadView('company.leave_balances.export_pdf', [
                    'headers' => $headers,
                    'rows' => $rows
                ])->download($fileName);
            }
        }

        // Get the filtered results with pagination
        $employees = $query->orderBy('employees.name')
                          ->paginate(25)
                          ->appends($request->except('page'));

        return view('company.leave_balances.index', compact('employees', 'departments', 'leaveTypes', 'currentYear'));
    }

    /**
     * Show the form for allocating leave balances.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $employees = Employee::where('company_id', $companyId)->get();
        $leaveTypes = LeaveType::where('company_id', $companyId)->where('is_active', true)->get();
        $currentYear = Carbon::now()->year;

        return view('company.leave_balances.create', compact('employees', 'leaveTypes', 'currentYear'));
    }

    /**
     * Store a newly created leave balance in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'total_days' => 'required|integer|min:0',
            'year' => 'required|integer|min:' . Carbon::now()->year,
        ]);

        // Check if employee belongs to the company
        $employee = Employee::findOrFail($validated['employee_id']);
        if ($employee->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if leave type belongs to the company
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        if ($leaveType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if balance already exists for this employee, leave type, and year
        $existingBalance = LeaveBalance::where('employee_id', $validated['employee_id'])
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $validated['year'])
            ->first();

        if ($existingBalance) {
            $existingBalance->update([
                'total_days' => $validated['total_days'],
            ]);

            $message = 'Leave balance updated successfully.';
        } else {
            LeaveBalance::create($validated);
            $message = 'Leave balance allocated successfully.';
        }

        return redirect()->route('leaves.leave-balances.index')
            ->with('success', $message);
    }

    /**
     * Bulk allocate leave balances to multiple employees.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkAllocate(Request $request)
    {
        $validated = $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'total_days' => 'required|integer|min:0',
            'year' => 'required|integer|min:' . Carbon::now()->year,
        ]);

        $companyId = Auth::user()->company_id;

        // Check if leave type belongs to the company
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        if ($leaveType->company_id !== $companyId) {
            abort(403, 'Unauthorized action.');
        }

        // Get all employees that belong to the company
        $validEmployeeIds = Employee::whereIn('id', $validated['employee_ids'])
            ->where('company_id', $companyId)
            ->pluck('id')
            ->toArray();

        foreach ($validEmployeeIds as $employeeId) {
            // Check if balance already exists for this employee, leave type, and year
            $existingBalance = LeaveBalance::where('employee_id', $employeeId)
                ->where('leave_type_id', $validated['leave_type_id'])
                ->where('year', $validated['year'])
                ->first();

            if ($existingBalance) {
                $existingBalance->update([
                    'total_days' => $validated['total_days'],
                ]);
            } else {
                LeaveBalance::create([
                    'employee_id' => $employeeId,
                    'leave_type_id' => $validated['leave_type_id'],
                    'total_days' => $validated['total_days'],
                    'year' => $validated['year'],
                ]);
            }
        }

        return redirect()->route('leaves.leave-balances.index')
            ->with('success', 'Leave balances allocated successfully.');
    }

    /**
     * Show the form for editing the specified leave balance.
     *
     * @param  \App\Models\LeaveBalance  $leaveBalance
     * @return \Illuminate\Http\Response
     */
    public function edit($encryptedId)
    {
        $leaveBalance = $this->getLeaveBalanceFromEncryptedId($encryptedId);
        // Check if leave balance belongs to an employee in the company
        if ($leaveBalance->employee->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $leaveTypes = LeaveType::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->get();

        return view('company.leave_balances.edit', compact('leaveBalance', 'leaveTypes'));
    }

    /**
     * Update the specified leave balance in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveBalance  $leaveBalance
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $encryptedId)
    {
        $leaveBalance = $this->getLeaveBalanceFromEncryptedId($encryptedId);
        // Check if leave balance belongs to an employee in the company
        if ($leaveBalance->employee->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'total_days' => 'required|integer|min:' . $leaveBalance->used_days,
        ]);

        $leaveBalance->update($validated);

        return redirect()->route('leaves.leave-balances.index')
            ->with('success', 'Leave balance updated successfully.');
    }
}
