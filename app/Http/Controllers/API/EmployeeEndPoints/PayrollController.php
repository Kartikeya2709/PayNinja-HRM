<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\Controller;
use App\Models\EmployeeSalary;
use App\Models\Payroll;
use App\Models\PayrollRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    /**
     * Get employee's current salary details
     */
    public function getCurrentSalary()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $currentSalary = $employee->currentSalary;

        if (!$currentSalary) {
            return response()->json(['message' => 'No salary record found'], 404);
        }

        return response()->json([
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'salary' => [
                'id' => $currentSalary->id,
                'basic_salary' => $currentSalary->basic_salary,
                // 'hra' => $currentSalary->hra,
                // 'da' => $currentSalary->da,
                // 'other_allowances' => $currentSalary->other_allowances,
                // 'gross_salary' => $currentSalary->gross_salary,
                // 'pf_deduction' => $currentSalary->pf_deduction,
                // 'esi_deduction' => $currentSalary->esi_deduction,
                // 'tds_deduction' => $currentSalary->tds_deduction,
                // 'professional_tax' => $currentSalary->professional_tax,
                // 'loan_deductions' => $currentSalary->loan_deductions,
                // 'total_deductions' => $currentSalary->total_deductions,
                'ctc' => $currentSalary->ctc,
                // 'net_salary' => $currentSalary->net_salary,
                // 'currency' => $currentSalary->currency,
                // 'payment_method' => $currentSalary->payment_method,
                // 'payment_frequency' => $currentSalary->payment_frequency,
                // 'bank_name' => $currentSalary->bank_name,
                // 'account_number' => $currentSalary->account_number,
                // 'ifsc_code' => $currentSalary->ifsc_code,
                // 'effective_from' => $currentSalary->effective_from,
                // 'effective_to' => $currentSalary->effective_to,
                // 'status' => $currentSalary->status,
                'notes' => $currentSalary->notes,
            ]
        ]);
    }

    /**
     * Get employee's payroll records
     */
    public function getPayrollRecords(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee){
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $query = Payroll::where('employee_id', $employee->id)
            ->where('company_id', $employee->company_id)
            ->where('status', 'paid')
            ->orderBy('payment_date', 'desc');

        // Filter by year if provided
        if ($request->has('year')) {
            $year = $request->year;
            $query->whereYear('payment_date', $year);
        }

        // Filter by month if provided
        if ($request->has('month')) {
            $month = $request->month;
            $query->whereMonth('payment_date', $month);
        }

        $payrollRecords = $query->get()->map(function ($record) {
            return [
                'id' => $record->id,
                'pay_period_start' => $record->pay_period_start,
                'pay_period_end' => $record->pay_period_end,
                'payment_date' => $record->payment_date,
                'gross_salary' => $record->gross_salary,
                'net_salary' => $record->net_salary,
                'total_deductions' => $record->total_deductions,
                'status' => $record->status,
                'notes' => $record->notes,
            ];
        });

        return response()->json([
            'payroll_records' => $payrollRecords
        ]);
    }

    /**
     * Get specific payroll record details
     */
    // public function getPayrollRecord($id)
    // {
    //     $user = Auth::user();
    //     $employee = $user->employee;

    //     if (!$employee) {
    //         return response()->json(['message' => 'Employee record not found'], 404);
    //     }

    //     $payrollRecord = Payroll::where('id', $id)
    //         ->where('employee_id', $employee->id)
    //         ->first();

    //     if (!$payrollRecord) {
    //         // Detect if the request came from API or web
    //         if (request()->expectsJson() || request()->is('api/*')) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Invalid payroll record ID or record not found.'
    //             ], 404);
    //         }

    //         // Redirect back for web routes
    //         return redirect()->back()->with('error', 'Invalid payroll record ID or record not found.');
    //     }

    //     return response()->json([
    //         'payroll_record' => [
    //             'id' => $payrollRecord->id,
    //             'pay_period_start' => $payrollRecord->pay_period_start,
    //             'pay_period_end' => $payrollRecord->pay_period_end,
    //             'payment_date' => $payrollRecord->payment_date,
    //             'basic_salary' => $payrollRecord->basic_salary,
    //             'hra' => $payrollRecord->hra,
    //             'da' => $payrollRecord->da,
    //             'other_allowances' => $payrollRecord->other_allowances,
    //             'gross_salary' => $payrollRecord->gross_salary,
    //             'pf_deduction' => $payrollRecord->pf_deduction,
    //             'esi_deduction' => $payrollRecord->esi_deduction,
    //             'professional_tax' => $payrollRecord->professional_tax,
    //             'tds' => $payrollRecord->tds,
    //             'leave_deductions' => $payrollRecord->leave_deductions,
    //             'late_attendance_deductions' => $payrollRecord->late_attendance_deductions,
    //             'other_deductions' => $payrollRecord->other_deductions,
    //             'net_salary' => $payrollRecord->net_salary,
    //             'status' => $payrollRecord->status,
    //             'present_days' => $payrollRecord->present_days,
    //             'leave_days' => $payrollRecord->leave_days,
    //             'overtime_hours' => $payrollRecord->overtime_hours,
    //             'overtime_amount' => $payrollRecord->overtime_amount,
    //             'incentives' => $payrollRecord->incentives,
    //             'bonus' => $payrollRecord->bonus,
    //             'advance_salary' => $payrollRecord->advance_salary,
    //             'notes' => $payrollRecord->notes,
    //             'total_deductions' => $payrollRecord->total_deductions,
    //             'total_earnings' => $payrollRecord->total_earnings,
    //         ]
    //     ]);
    // }

    public function downloadPayslip($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $payrollRecord = Payroll::where('id', $id)
            ->where('employee_id', $employee->id)
            ->first();

        if (!$payrollRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid payroll record ID or record not found.'
            ], 404);
        }

        // Eager load necessary relationships
        $payrollRecord->load([
            'items' => function($query) {
                $query->orderBy('type')->orderBy('id');
            },
            'company',
            'employee.department',
            'employee.designation'
        ]);

        // Prepare data for the PDF
        $data = [
            'payroll' => $payrollRecord,
            'employee' => $employee,
            'monthYear' => $payrollRecord->pay_period_start,
            'generatedDate' => now()->format('M d, Y')
        ];

        // Generate PDF using PDF facade
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payslip', $data);
        $filename = 'payslip-' . $employee->employee_id . '-' .
                   $payrollRecord->pay_period_start->format('M-Y') . '.pdf';

        // Return the PDF as a download
        return $pdf->download($filename);
    }
}
