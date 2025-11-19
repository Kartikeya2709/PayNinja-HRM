<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeAttendanceAdjustment;
use App\Models\PayrollSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PayrollCalculationService
{
    protected $payrollSettings;

    public function __construct()
    {
        // Settings will be loaded per company when calculateMonthlyPayroll is called
    }
    
    /**
     * Ensure payroll settings exist for a company
     * 
     * @param int $companyId
     * @return PayrollSetting
     */
    protected function ensurePayrollSettingsExist($companyId)
     {
         return PayrollSetting::firstOrCreate(
             ['company_id' => $companyId],
             [
                 'days_in_month' => 30,
                 'deductible_leave_type_ids' => [],
                 'enable_halfday_deduction' => true,
                 'enable_reimbursement' => true
             ]
         );
     }

    /**
     * Calculate monthly payroll with half-day deductions and reimbursements
     *
     * @param Employee $employee
     * @param int $month
     * @param int $year
     * @param int|null $companyId
     * @return array
     */
    public function calculateMonthlyPayroll(Employee $employee, $month, $year, $companyId = null)
    {
        try {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            // Use provided company ID or fall back to employee's company
            $companyId = $companyId ?? $employee->company_id;
            
            // Ensure payroll settings exist for the company
            $this->payrollSettings = $this->ensurePayrollSettingsExist($companyId);
            
            $basicSalary = $employee->current_salary;
            $halfDayDeduction = 0;
            $reimbursementAmount = 0;
            $halfDays = 0;
            
            // Calculate half day deductions if enabled
            // if ($this->payrollSettings->enable_halfday_deduction) {
            //     $halfDays = $employee->halfDays()
            //         ->whereBetween('date', [$startDate, $endDate])
            //         ->approved()
            //         ->count();

                if ($halfDays > 0) {
                    $daysInMonth = $this->payrollSettings->days_in_month ?: 30;
                    $dailyRate = $basicSalary / $daysInMonth;
                    $halfDayDeduction = ($dailyRate / 2) * $halfDays;
                }
            // }

            // Calculate reimbursements if enabled
            if ($this->payrollSettings->enable_reimbursement) {
                $reimbursementAmount = $employee->reimbursements()
                ->whereBetween('expense_date', [$startDate, $endDate])
                    // ->approved()
                    ->where('status', 'admin_approved')
                    ->sum('amount');
            }
            
            // Calculate net salary
            $netSalary = max(0, $basicSalary - $halfDayDeduction + $reimbursementAmount);
            
            return [
                'basic_salary' => $basicSalary,
                'half_days' => $halfDays,
                'half_day_deduction' => round($halfDayDeduction, 2),
                'reimbursement_amount' => round($reimbursementAmount, 2),
                'net_salary' => round($netSalary, 2),
                'period' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                    'month' => $month,
                    'year' => $year,
                    'days_in_month' => $this->payrollSettings->days_in_month ?: 30
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Payroll calculation error: ' . $e->getMessage(), [
                'employee_id' => $employee->id,
                'month' => $month,
                'year' => $year,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a minimal response with error information
            return [
                'error' => true,
                'message' => 'Error calculating payroll: ' . $e->getMessage(),
                'basic_salary' => 0,
                'half_days' => 0,
                'half_day_deduction' => 0,
                'reimbursement_amount' => 0,
                'net_salary' => 0,
                'period' => [
                    'start' => $startDate->format('Y-m-d') ?? null,
                    'end' => $endDate->format('Y-m-d') ?? null,
                    'month' => $month,
                    'year' => $year
                ]
            ];
        }
    }
    
    /**
     * Calculate half-day rate based on employee's salary and company settings
     * 
     * @param Employee $employee
     * @param int|null $companyId
     * @return float
     */
    /**
     * Get the current payroll settings being used
     * 
     * @return PayrollSetting
     */
    public function getPayrollSettings()
    {
        if (!$this->payrollSettings) {
            // Return default settings if none set
            return new PayrollSetting([
                'days_in_month' => 30,
                'deductible_leave_type_ids' => [],
                'enable_halfday_deduction' => true,
                'enable_reimbursement' => true
            ]);
        }
        return $this->payrollSettings;
    }
    
    /**
     * Calculate half-day rate based on employee's salary and company settings
     * 
     * @param Employee $employee
     * @param int|null $companyId
     * @return float
     */
    public function getHalfDayRate(Employee $employee, $companyId = null)
    {
        $companyId = $companyId ?? $employee->company_id;
        
        // Get or create payroll settings for the company
        $settings = PayrollSetting::firstOrCreate(
            ['company_id' => $companyId],
            ['days_in_month' => 30]
        );
        
        $daysInMonth = $settings->days_in_month ?: 30;
        return round(($employee->current_salary / $daysInMonth) / 2, 2);
    }
}
