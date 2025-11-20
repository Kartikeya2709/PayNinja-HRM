<?php

namespace App\Http\Controllers;

use App\Models\EmployeeSalary;
use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayslipController extends Controller
{
    /**
     * Get the color class for a payroll status
     *
     * @param string $status
     * @return string
     */
    protected function getStatusColor($status)
    {
        switch (strtolower($status)) {
            case 'paid':
                return 'success';
            case 'pending':
                return 'warning';
            case 'cancelled':
                return 'danger';
            case 'processing':
                return 'info';
            default:
                return 'secondary';
        }
    }

    /**
     * Display a list of available payslips for the employee
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function listPayslips()
    {
        $user = Auth::user();
        
        if (!$user->employee) {
            abort(403, 'Employee record not found.');
        }
        
        $employee = $user->employee;
        $currentSalary = $employee->currentSalary;
        
        if (!$currentSalary) {
            // return redirect()->route('employee.salary.details')
                // ->with('error', 'No active salary record found.');
                return redirect()->back()
                    ->with('error', 'No active salary record found.');
        }
        
        // Get all payroll records for the employee
        $payrolls = Payroll::with('processedBy')
            ->where('employee_id', $employee->id)
            ->where('status', '=', 'Paid')
            ->orderBy('pay_period_end', 'desc')
            ->orderBy('pay_period_start', 'desc')
            ->paginate(12); // Show 12 payslips per page
            
        // Transform the payroll data for the view
        $payslips = $payrolls->map(function($payroll) use ($employee) {
            $payPeriod = $payroll->pay_period_start->format('M d, Y') . ' - ' . 
                        ($payroll->pay_period_end ? $payroll->pay_period_end->format('M d, Y') : 'Present');
            
            return [
                'id' => $payroll->id,
                'pay_period' => $payPeriod,
                'pay_period_start' => $payroll->pay_period_start,
                'pay_period_end' => $payroll->pay_period_end,
                'gross_salary' => $payroll->gross_salary,
                'total_deductions' => $payroll->total_deductions,
                'net_salary' => $payroll->net_salary,
                'status' => $payroll->status,
                'status_color' => $this->getStatusColor($payroll->status),
                'payment_date' => $payroll->payment_date,
                'processed_by' => $payroll->processedBy ? $payroll->processedBy->name : 'System',
                'employee_id' => $employee->id,
                'view_url' => route('employee.payroll.show', $payroll->id),
                'download_url' => route('employee.payroll.download', $payroll->id),
            ];
        });
            
        return view('employee.salary.payslips', [
            'salary' => $currentSalary,
            'payslips' => $payslips,
            'payrolls' => $payrolls, // Pass the paginator instance for the view
            'employee' => $employee,
            'currentMonthYear' => now()->format('Y-m')
        ]);
    }

    // ... rest of the file remains the same ...
}
