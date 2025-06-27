<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSalary;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;

class PayrollCalculator
{
    /**
     * Calculate payroll for an employee for a specific pay period.
     *
     * @param Employee $employee
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function calculatePayroll(Employee $employee, $startDate, $endDate)
    {
        try {
            // Log the start of payroll calculation
            Log::info('Starting payroll calculation', [
                'employee_id' => $employee->id,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            // Get the employee's current salary
            $salary = $employee->currentSalary;
            
            if (!$salary) {
                $msg = "No active salary record found for employee ID: {$employee->id}";
                Log::error($msg);
                throw new \Exception($msg);
            }
            
            Log::info('Using salary record', [
                'employee_id' => $employee->id,
                'salary_id' => $salary->id,
                'gross_salary' => $salary->gross_salary,
                'basic_salary' => $salary->basic_salary
            ]);
            
            // Convert string dates to Carbon instances
            $startDate = Carbon::parse($startDate);
            $endDate = Carbon::parse($endDate);
            
            // Calculate working days and leave days
            $attendanceData = $this->calculateAttendance($employee, $startDate, $endDate);
            
            // Calculate basic salary for the period (pro-rated if needed)
            $basicSalary = $this->calculateBasicSalary($salary, $attendanceData['working_days'], $attendanceData['total_days']);
            
            // Calculate HRA, DA based on basic salary
            $hra = $this->calculateHra($salary, $basicSalary);
            $da = $this->calculateDa($salary, $basicSalary);
            
            // Calculate other earnings
            $otherAllowances = $this->calculateOtherAllowances($salary, $attendanceData['working_days'], $attendanceData['total_days']);
            
            // Calculate gross salary
            $grossSalary = $basicSalary + $hra + $da + $otherAllowances;
            
            // Calculate deductions
            $pfDeduction = $this->calculatePfDeduction($salary, $basicSalary);
            $esiDeduction = $this->calculateEsiDeduction($salary, $grossSalary);
            $professionalTax = $this->calculateProfessionalTax($salary, $grossSalary);
            $tds = $this->calculateTds($salary, $grossSalary);
            
            // Calculate leave deductions
            Log::info('Calculating leave deductions', [
                'employee_id' => $employee->id,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);
            
            $leaveDeductions = $this->calculateLeaveDeductions($employee, $startDate, $endDate, $salary);
            
            Log::info('Leave deductions calculated', [
                'employee_id' => $employee->id,
                'leave_deductions' => $leaveDeductions
            ]);
            $lateAttendanceDeductions = $this->calculateLateAttendanceDeductions($employee, $startDate, $endDate, $salary);
            $otherDeductions = $this->calculateOtherDeductions($employee, $startDate, $endDate);
            
            // Calculate total deductions
            $totalDeductions = $pfDeduction + $esiDeduction + $professionalTax + $tds + 
                              $leaveDeductions + $lateAttendanceDeductions + $otherDeductions;
            
            // Calculate net salary
            $netSalary = $grossSalary - $totalDeductions;
            
            // Add any overtime, incentives, bonus, etc.
            $overtime = $this->calculateOvertime($employee, $startDate, $endDate, $salary);
            $incentives = $this->calculateIncentives($employee, $startDate, $endDate);
            $bonus = $this->calculateBonus($employee, $startDate, $endDate);
            $advanceSalary = $this->getAdvanceSalary($employee, $startDate, $endDate);
            
            // Adjust net salary with additional earnings and deductions
            $netSalary += $overtime['amount'] + $incentives + $bonus - $advanceSalary;
            
            // Ensure net salary is not negative
            $netSalary = max(0, $netSalary);
            
            return [
                'employee_id' => $employee->id,
                'pay_period_start' => $startDate->format('Y-m-d'),
                'pay_period_end' => $endDate->format('Y-m-d'),
                'payment_date' => null, // To be set when marking as paid
                'basic_salary' => round($basicSalary, 2),
                'hra' => round($hra, 2),
                'da' => round($da, 2),
                'other_allowances' => round($otherAllowances, 2),
                'gross_salary' => round($grossSalary, 2),
                'pf_deduction' => round($pfDeduction, 2),
                'esi_deduction' => round($esiDeduction, 2),
                'professional_tax' => round($professionalTax, 2),
                'tds' => round($tds, 2),
                'leave_deductions' => round($leaveDeductions, 2),
                'late_attendance_deductions' => round($lateAttendanceDeductions, 2),
                'other_deductions' => round($otherDeductions, 2),
                'net_salary' => round($netSalary, 2),
                'present_days' => $attendanceData['present_days'],
                'leave_days' => $attendanceData['leave_days'],
                'overtime_hours' => $overtime['hours'],
                'overtime_amount' => round($overtime['amount'], 2),
                'incentives' => round($incentives, 2),
                'bonus' => round($bonus, 2),
                'advance_salary' => round($advanceSalary, 2),
                'status' => PayrollRecord::STATUS_DRAFT,
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating payroll: ' . $e->getMessage(), [
                'employee_id' => $employee->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Calculate attendance data for the pay period.
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    private function calculateAttendance(Employee $employee, Carbon $startDate, Carbon $endDate)
    {
        // Get total days in the period
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        // Get working days (excluding weekends and holidays)
        $workingDays = $this->getWorkingDays($startDate, $endDate);
        
        // Get all attendance records for the period
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();
            
        // Count present days (including holidays marked as present)
        $presentDays = $attendances->where('status', 'present')->count();
        
        // Count holidays (exclude them from absent days)
        $holidayDays = $attendances->where('status', 'Holiday')->count();
        
        // Get approved leave days
        $leaveDays = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->sum('days');
            
        // Adjust working days by subtracting holidays
        $workingDays -= $holidayDays;
        
        return [
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'leave_days' => $leaveDays,
        ];
    }
    
    /**
     * Get working days in the period (excluding weekends and holidays).
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return int
     */
    private function getWorkingDays(Carbon $startDate, Carbon $endDate)
    {
        $days = 0;
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            // Skip weekends (Saturday and Sunday)
            if ($date->isWeekend()) {
                continue;
            }
            
            // Check if it's a holiday
            $isHoliday = \App\Models\Holiday::where('date', $date->format('Y-m-d'))->exists();
            
            if (!$isHoliday) {
                $days++;
            }
        }
        
        return $days;
    }
    
    /**
     * Calculate basic salary for the period.
     *
     * @param EmployeeSalary $salary
     * @param int $workingDays
     * @param int $totalDays
     * @return float
     */
    private function calculateBasicSalary(EmployeeSalary $salary, $workingDays, $totalDays)
    {
        // If working days is less than total days, pro-rate the salary
        if ($workingDays < $totalDays) {
            $daysInMonth = Carbon::now()->daysInMonth;
            return ($salary->basic_salary / $daysInMonth) * $workingDays;
        }
        
        return $salary->basic_salary;
    }
    
    /**
     * Calculate HRA (House Rent Allowance).
     *
     * @param EmployeeSalary $salary
     * @param float $basicSalary
     * @return float
     */
    private function calculateHra(EmployeeSalary $salary, $basicSalary)
    {
        return $salary->hra;
    }
    
    /**
     * Calculate DA (Dearness Allowance).
     *
     * @param EmployeeSalary $salary
     * @param float $basicSalary
     * @return float
     */
    private function calculateDa(EmployeeSalary $salary, $basicSalary)
    {
        return $salary->da;
    }
    
    /**
     * Calculate other allowances.
     *
     * @param EmployeeSalary $salary
     * @param int $workingDays
     * @param int $totalDays
     * @return float
     */
    private function calculateOtherAllowances(EmployeeSalary $salary, $workingDays, $totalDays)
    {
        // If working days is less than total days, pro-rate the allowances
        if ($workingDays < $totalDays) {
            $daysInMonth = Carbon::now()->daysInMonth;
            return ($salary->other_allowances / $daysInMonth) * $workingDays;
        }
        
        return $salary->other_allowances;
    }
    
    /**
     * Calculate PF (Provident Fund) deduction.
     *
     * @param EmployeeSalary $salary
     * @param float $basicSalary
     * @return float
     */
    private function calculatePfDeduction(EmployeeSalary $salary, $basicSalary)
    {
        return $salary->pf_deduction;
    }
    
    /**
     * Calculate ESI (Employee State Insurance) deduction.
     *
     * @param EmployeeSalary $salary
     * @param float $grossSalary
     * @return float
     */
    private function calculateEsiDeduction(EmployeeSalary $salary, $grossSalary)
    {
        return $salary->esi_deduction;
    }
    
    /**
     * Calculate Professional Tax.
     *
     * @param EmployeeSalary $salary
     * @param float $grossSalary
     * @return float
     */
    private function calculateProfessionalTax(EmployeeSalary $salary, $grossSalary)
    {
        return $salary->professional_tax;
    }
    
    /**
     * Calculate TDS (Tax Deducted at Source).
     *
     * @param EmployeeSalary $salary
     * @param float $grossSalary
     * @return float
     */
    private function calculateTds(EmployeeSalary $salary, $grossSalary)
    {
        return $salary->tds_deduction ?? 0;
    }
    
    /**
     * Calculate leave deductions.
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param EmployeeSalary $salary
     * @return float
     */
    private function calculateLeaveDeductions(Employee $employee, Carbon $startDate, Carbon $endDate, EmployeeSalary $salary)
    {
        Log::info('Starting calculateLeaveDeductions', [
            'employee_id' => $employee->id,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'employee_name' => $employee->name
        ]);
        
        // Get company's payroll settings to check deductible leave types
        $payrollSettings = $employee->company->payrollSetting;
        
        if (!$payrollSettings) {
            Log::warning('No payroll settings found for company', [
                'company_id' => $employee->company_id,
                'company_name' => $employee->company->name
            ]);
            return 0;
        }
        
        if (empty($payrollSettings->deductible_leave_type_ids)) {
            Log::info('No deductible leave types configured', [
                'company_id' => $employee->company_id,
                'payroll_settings_id' => $payrollSettings->id
            ]);
            return 0; // No deductible leave types configured
        }
        
        Log::info('Deductible leave types', [
            'deductible_leave_type_ids' => $payrollSettings->deductible_leave_type_ids
        ]);
        
        // Get all approved leave requests for deductible leave types within the pay period
        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereIn('leave_type_id', $payrollSettings->deductible_leave_type_ids)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                          ->where('end_date', '>=', $endDate);
                    });
            })
            ->with('leaveType') // Eager load the leaveType relationship
            ->get();
            
        Log::info('Found leave requests', [
            'employee_id' => $employee->id,
            'leave_requests_count' => $leaveRequests->count(),
            'leave_requests' => $leaveRequests->map(function($req) {
                return [
                    'id' => $req->id,
                    'leave_type_id' => $req->leave_type_id,
                    'leave_type_name' => $req->leaveType->name ?? 'Unknown',
                    'start_date' => $req->start_date,
                    'end_date' => $req->end_date,
                    'status' => $req->status
                ];
            })
        ]);
        
        $totalDeduction = 0;
        $daysInMonth = $startDate->daysInMonth;
        $deductionPerDay = $salary->gross_salary / $daysInMonth;
        
        Log::info('Deduction calculation parameters', [
            'gross_salary' => $salary->gross_salary,
            'days_in_month' => $daysInMonth,
            'deduction_per_day' => $deductionPerDay
        ]);
        
        foreach ($leaveRequests as $leave) {
            // Calculate the overlapping days between leave and pay period
            $leaveStart = Carbon::parse($leave->start_date)->startOfDay();
            $leaveEnd = Carbon::parse($leave->end_date)->endOfDay();
            
            $periodStart = $startDate->copy()->startOfDay();
            $periodEnd = $endDate->copy()->endOfDay();
            
            // Adjust dates to only include the overlapping period
            $effectiveStart = $leaveStart->gt($periodStart) ? $leaveStart : $periodStart;
            $effectiveEnd = $leaveEnd->lt($periodEnd) ? $leaveEnd : $periodEnd;
            
            // Calculate number of working days in the overlapping period
            $days = 0;
            $current = $effectiveStart->copy();
            $workDays = [];
            
            Log::info('Processing leave request', [
                'leave_id' => $leave->id,
                'leave_type' => $leave->leaveType->name ?? 'Unknown',
                'leave_start' => $leaveStart->toDateString(),
                'leave_end' => $leaveEnd->toDateString(),
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'effective_start' => $effectiveStart->toDateString(),
                'effective_end' => $effectiveEnd->toDateString()
            ]);
            
            while ($current->lte($effectiveEnd)) {
                // Skip weekends (Saturday = 6, Sunday = 0)
                if (!in_array($current->dayOfWeek, [0, 6])) {
                    $days++;
                    $workDays[] = $current->toDateString();
                }
                $current->addDay();
            }
            
            $deductionForThisLeave = $days * $deductionPerDay;
            $totalDeduction += $deductionForThisLeave;
            
            Log::info('Leave deduction calculated', [
                'leave_id' => $leave->id,
                'working_days' => $days,
                'work_days_list' => $workDays,
                'deduction_per_day' => $deductionPerDay,
                'deduction_for_this_leave' => $deductionForThisLeave,
                'running_total_deduction' => $totalDeduction
            ]);
        }
        
        Log::info('Total leave deductions calculated', [
            'employee_id' => $employee->id,
            'total_deduction' => $totalDeduction
        ]);
        
        return $totalDeduction;
    }
    
    /**
     * Calculate late attendance deductions.
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param EmployeeSalary $salary
     * @return float
     */
    private function calculateLateAttendanceDeductions(Employee $employee, Carbon $startDate, Carbon $endDate, EmployeeSalary $salary)
    {
        // Get late attendance count
        $lateAttendanceCount = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('status', 'late')
            ->count();
        
        // Calculate deduction per late attendance (e.g., 0.5 day's salary)
        $daysInMonth = Carbon::now()->daysInMonth;
        $deductionPerDay = $salary->gross_salary / $daysInMonth;
        $deductionPerLate = $deductionPerDay * 0.5;
        
        return $deductionPerLate * $lateAttendanceCount;
    }
    
    /**
     * Calculate other deductions.
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function calculateOtherDeductions(Employee $employee, Carbon $startDate, Carbon $endDate)
    {
        // For now, return 0. This can be extended to include other deductions like loans, advances, etc.
        return 0;
    }
    
    /**
     * Calculate overtime.
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param EmployeeSalary $salary
     * @return array
     */
    private function calculateOvertime(Employee $employee, Carbon $startDate, Carbon $endDate, EmployeeSalary $salary)
    {
        // Get overtime hours from attendance records
        $overtimeHours = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->sum('overtime_hours');
        
        // Calculate overtime rate (e.g., 1.5 times hourly rate)
        $daysInMonth = Carbon::now()->daysInMonth;
        $hoursPerDay = 8; // Assuming 8 hours per day
        $hourlyRate = $salary->basic_salary / ($daysInMonth * $hoursPerDay);
        $overtimeRate = $hourlyRate * 1.5;
        
        $overtimeAmount = $overtimeHours * $overtimeRate;
        
        return [
            'hours' => $overtimeHours,
            'amount' => $overtimeAmount,
        ];
    }
    
    /**
     * Calculate incentives.
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function calculateIncentives(Employee $employee, Carbon $startDate, Carbon $endDate)
    {
        // For now, return 0. This can be extended to include performance-based incentives.
        return 0;
    }
    
    /**
     * Calculate bonus.
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function calculateBonus(Employee $employee, Carbon $startDate, Carbon $endDate)
    {
        // For now, return 0. This can be extended to include bonuses.
        return 0;
    }
    
    /**
     * Get advance salary.
     *
     * @param Employee $employee
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function getAdvanceSalary(Employee $employee, Carbon $startDate, Carbon $endDate)
    {
        // For now, return 0. This can be extended to include advance salary tracking.
        return 0;
    }
}
