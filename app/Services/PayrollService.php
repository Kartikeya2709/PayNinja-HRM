<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BeneficiaryBadge;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeBeneficiaryBadge;
use App\Models\EmployeeSalary;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollSetting;
use App\Models\Reimbursement;
use App\Services\AttendanceService;
use App\Services\PayrollCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollService
{
    protected AttendanceService $attendanceService;
    protected PayrollCalculationService $payrollCalculationService;

    public function __construct(AttendanceService $attendanceService, PayrollCalculationService $payrollCalculationService)
    {
        $this->attendanceService = $attendanceService;
        $this->payrollCalculationService = $payrollCalculationService;
    }

    /**
     * Generate payroll for a specific employee for a given pay period.
     *
     * @param Employee $employee
     * @param Carbon $payPeriodStart
     * @param Carbon $payPeriodEnd
     * @param Company $company
     * @param array $options (e.g., manual overrides, specific instructions)
     * @return Payroll|null
     * @throws Exception
     */
    public function generatePayrollForEmployee(Employee $employee, Carbon $payPeriodStart, Carbon $payPeriodEnd, Company $company, array $options = []): ?Payroll
    {
        Log::info("PayrollService: Starting generatePayrollForEmployee for Employee ID: {$employee->id}", [
            'employee_id' => $employee->id,
            'pay_period_start' => $payPeriodStart->toDateString(),
            'pay_period_end' => $payPeriodEnd->toDateString(),
            'company_id' => $company->id,
            'options' => $options
        ]);

        // Prevent duplicate payroll generation for the same employee and period
        $existingPayroll = Payroll::where('employee_id', $employee->id)
            ->where('pay_period_start', $payPeriodStart->toDateString())
            ->where('pay_period_end', $payPeriodEnd->toDateString())
            ->where('status', '!=', 'cancelled') // Allow re-generation if previous was cancelled
            ->first();

        if ($existingPayroll) {
            // Optionally, update existing or throw error. For now, let's skip if 'paid' or 'processed'.
            if (in_array($existingPayroll->status, ['paid', 'processed'])) {
                throw new \Exception("Payroll for {$employee->user->name} for this period ({$payPeriodStart->toDateString()} - {$payPeriodEnd->toDateString()}) is already {$existingPayroll->status}.");
            }
            // If pending, maybe delete and regenerate or update. For now, let's throw an error.
            throw new \Exception("A pending payroll already exists for {$employee->user->name} for this period.");
        }

        DB::beginTransaction();
        try {
            // 1. Fetch Base Salary
            // Assuming a method on Employee model or a SalaryService
            $salaryDetails = $this->getEmployeeSalaryDetails($employee);
            Log::debug("PayrollService: Fetched salary details for Employee ID: {$employee->id}", ['salary_details' => $salaryDetails->toArray()]);
            if (!$salaryDetails) {
                throw new \Exception("Base salary details not found for employee ID: {$employee->id}.");
            }
            $payrollItems = [];

            // Ensure EmployeeDetail and assigned beneficiary badges are loaded
            $employee->loadMissing(['employeeDetail', 'assignedBeneficiaryBadges.beneficiaryBadge']);

            $ctcAnnual = $employee->employeeDetail->ctc ?? 0;
            $ctcMonthly = $ctcAnnual > 0 ? $ctcAnnual / 12 : 0;
            Log::debug("PayrollService: Employee CTC Details for Employee ID: {$employee->id}", ['ctc_annual' => $ctcAnnual, 'ctc_monthly' => $ctcMonthly]);

            // Add Earning Components from EmployeeSalary
            $earningComponents = [
                'basic_salary' => ['Basic Salary', true]
            ];

            foreach ($earningComponents as $field => $details) {
                if (isset($salaryDetails->{$field}) && $salaryDetails->{$field} > 0) {
                    $amount = (float) $salaryDetails->{$field};
                    $payrollItems[] = [
                        'type' => 'earning',
                        'description' => $details[0],
                        'amount' => $amount,
                        'is_taxable' => $details[1],
                        'is_fixed' => true, // Assuming these are fixed components from salary structure
                    ];
                }
            }

            // Add Deduction Components from EmployeeSalary
            $deductionComponents = [
                // Commented out the following deductions as per user request
                // 'pf_deduction' => ['Provident Fund', true], // True: Pre-tax deduction
                // 'esi_deduction' => ['ESI Contribution', true], // True: Pre-tax deduction
                // 'professional_tax' => ['Professional Tax', true], // True: Pre-tax deduction
                // 'tds_deduction' => ['TDS (Tax Deducted at Source)', false], // Tax itself
                // 'loan_deductions' => ['Loan Deduction', false], // Typically post-tax
                'other_deductions' => ['Other Deductions', false], // Keep other deductions
            ];

            // Log that we're skipping the standard deductions
            Log::info('Skipping standard deductions (PF, ESI, Professional Tax, TDS, Loan) as per user request', [
                'employee_id' => $employee->id,
                'pay_period_start' => $payPeriodStart->toDateString(),
                'pay_period_end' => $payPeriodEnd->toDateString()
            ]);

            foreach ($deductionComponents as $field => $details) {
                if (isset($salaryDetails->{$field}) && $salaryDetails->{$field} > 0) {
                    $amount = (float) $salaryDetails->{$field};
                    $payrollItems[] = [
                        'type' => 'deduction',
                        'description' => $details[0],
                        'amount' => $amount,
                        'is_taxable' => $details[1], 
                    ];
                }
            }

            $attendanceSettings = $this->attendanceService->getAttendanceSettings($employee->company_id);
            Log::debug("PayrollService: Fetched attendance settings for Company ID: {$employee->company_id}", ['attendance_settings' => (array) $attendanceSettings]);
            $attendanceData = $this->getAttendanceDataForPeriod($employee, $payPeriodStart, $payPeriodEnd);
            Log::debug("PayrollService: Fetched attendance data for Employee ID: {$employee->id}", ['attendance_data_count' => $attendanceData->count()]);

            $overtimePay = $this->calculateOvertimePay($employee, $attendanceData, $salaryDetails, $attendanceSettings);
            Log::debug("PayrollService: Calculated overtime pay for Employee ID: {$employee->id}", ['overtime_pay' => $overtimePay]);
            if ($overtimePay > 0) {
                $payrollItems[] = [
                    'type' => 'earning',
                    'description' => 'Overtime Pay',
                    'amount' => round((float) $overtimePay, 2),
                    'is_taxable' => true,
                    'is_fixed' => false // Overtime is variable
                ];
                Log::debug("PayrollService: Added Overtime Pay to payroll items for Employee ID: {$employee->id}", ['amount' => $overtimePay]);
            }
            // Example: Add bonus if applicable
            // $payrollItems[] = ['type' => 'bonus', 'name' => 'Performance Bonus', 'amount' => 5000.00, 'is_taxable' => true];

            // Process Beneficiary Badges (Allowances and Deductions)
            $basicSalaryMonthly = (float) ($salaryDetails->basic_salary ?? 0);
            $this->_processBeneficiaryBadges(
                $employee,
                $payPeriodStart,
                $payPeriodEnd,
                $ctcMonthly,
                $basicSalaryMonthly,
                $payrollItems // pass by reference
            );

            $latenessDeduction = $this->calculateLatenessDeduction($employee, $attendanceData, $salaryDetails, $attendanceSettings);
            Log::debug("PayrollService: Calculated lateness deduction for Employee ID: {$employee->id}", ['lateness_deduction' => $latenessDeduction]);
            if ($latenessDeduction > 0) {
                $payrollItems[] = ['type' => 'deduction', 'description' => 'Lateness Deduction', 'amount' => $latenessDeduction, 'is_taxable' => false];
            }

            // 3. Calculate Net Unpaid Days based on Attendance and Approved Leaves
            // Fetch approved leave requests for the period
            $approvedLeaveRequestsInPeriod = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function ($query) use ($payPeriodStart, $payPeriodEnd) {
                    $query->where('start_date', '<=', $payPeriodEnd->toDateString())
                          ->where('end_date', '>=', $payPeriodStart->toDateString());
                })
                ->with('leaveType') // Eager load for performance in the calculation method
                ->get();
            Log::debug("PayrollService: Fetched approved leave requests for Employee ID: {$employee->id}", ['approved_leave_requests_count' => $approvedLeaveRequestsInPeriod->count()]);

            // Calculate net unpaid days based on attendance and approved leaves
            Log::debug("PayrollService: Calling calculateAttendanceBasedUnpaidDays for Employee ID: {$employee->id}");
            $unpaidPortions = $this->calculateAttendanceBasedUnpaidDays(
                $employee,
                $payPeriodStart,
                $payPeriodEnd,
                $attendanceData, // Already fetched
                $approvedLeaveRequestsInPeriod,
                $this->attendanceService // Already injected
            );
            $netUnpaidLeaveDaysPortion = $unpaidPortions['unpaid_leave_days_portion'];
            $netAbsentDaysPortion = $unpaidPortions['absent_days_portion'];

            Log::info("PayrollService: Calculated unpaid portions for Employee ID: {$employee->id}", [
                'net_unpaid_leave_days_portion' => $netUnpaidLeaveDaysPortion,
                'net_absent_days_portion' => $netAbsentDaysPortion
            ]);

            // 1. Unpaid Leave Deduction (from approved unpaid leaves)
            if ($netUnpaidLeaveDaysPortion > 0) {
                $unpaidLeaveDeductionAmount = $this->calculateUnpaidLeaveDeduction($employee, $netUnpaidLeaveDaysPortion, $salaryDetails, $payPeriodStart);
                Log::info("PayrollService: Calculated Unpaid Leave Deduction for Employee ID: {$employee->id}", [
                    'unpaid_leave_days_portion' => $netUnpaidLeaveDaysPortion, 
                    'deduction_amount' => $unpaidLeaveDeductionAmount
                ]);
                if ($unpaidLeaveDeductionAmount > 0) {
                    $payrollItems[] = ['type' => 'deduction', 'description' => 'Unpaid Leave Deduction', 'amount' => $unpaidLeaveDeductionAmount, 'is_taxable' => false];
                }
            }

            // 2. Absent Days Deduction (from absences without approved leave)
            if ($netAbsentDaysPortion > 0) {
                $absentDayDeductionAmount = $this->calculateUnpaidLeaveDeduction($employee, $netAbsentDaysPortion, $salaryDetails, $payPeriodStart); // Re-use calculation logic
                Log::info("PayrollService: Calculated Absent Days Deduction for Employee ID: {$employee->id}", [
                    'absent_days_portion' => $netAbsentDaysPortion, 
                    'deduction_amount' => $absentDayDeductionAmount
                ]);
                if ($absentDayDeductionAmount > 0) {
                    $payrollItems[] = ['type' => 'deduction', 'description' => 'Absent Days Deduction', 'amount' => $absentDayDeductionAmount, 'is_taxable' => false];
                }
            }

            // Calculate half-day deductions and reimbursements using PayrollCalculationService
            // The service will handle getting/creating the payroll settings
            $calculation = $this->payrollCalculationService->calculateMonthlyPayroll(
                $employee, 
                $payPeriodStart->month, 
                $payPeriodStart->year,
                $company->id // Pass company ID to ensure correct settings are used
            );
            
            // Get the settings that were used in the calculation
            $payrollSettings = $this->payrollCalculationService->getPayrollSettings();
            
            // Apply half-day deductions if enabled and there are any
            if ($payrollSettings->enable_halfday_deduction && isset($calculation['half_day_deduction']) && $calculation['half_day_deduction'] > 0) {
                $payrollItems[] = [
                    'type' => 'deduction',
                    'description' => 'Half Day Deduction',
                    'amount' => $calculation['half_day_deduction'],
                    'is_taxable' => false
                ];

                // Log the deduction for debugging
                Log::debug('Applied half-day deduction', [
                    'employee_id' => $employee->id,
                    'half_days' => $calculation['half_days'] ?? 0,
                    'amount' => $calculation['half_day_deduction'],
                    'period' => $calculation['period'] ?? []
                ]);
            }

            // Handle reimbursements based on settings
            if ($payrollSettings->enable_reimbursement == true) {
                if ($calculation['reimbursement_amount'] > 0) {
                    $payrollItems[] = [
                        'type' => 'reimbursement',
                        'description' => 'Reimbursement',
                        'amount' => $calculation['reimbursement_amount'],
                        'is_taxable' => false // Assuming reimbursements are non-taxable by default
                    ];
                    
                    Log::info('Added reimbursement to payroll', [
                        'employee_id' => $employee->id,
                        'amount' => $calculation['reimbursement_amount'],
                        'pay_period' => $payPeriodStart->format('Y-m-d') . ' to ' . $payPeriodEnd->format('Y-m-d')
                    ]);
                }
            } else {
                Log::info('Skipping reimbursements in payroll calculation as per settings', [
                    'employee_id' => $employee->id,
                    'pay_period_start' => $payPeriodStart->toDateString(),
                    'pay_period_end' => $payPeriodEnd->toDateString()
                ]);
            }
            // 4. Calculate Deductible Leave Deductions
            $deductibleLeaveDeductions = $this->calculateDeductibleLeaveDeductions($employee, $payPeriodStart, $payPeriodEnd, $payrollItems);
            if ($deductibleLeaveDeductions > 0) {
                Log::info('Added deductible leave deductions to payroll', [
                    'employee_id' => $employee->id,
                    'deduction_amount' => $deductibleLeaveDeductions
                ]);
            }

            // 5. Fetch Approved Reimbursements (Skipped as per user request)
            // $approvedReimbursements = $this->getApprovedReimbursementsForPeriod($employee, $payPeriodStart, $payPeriodEnd);
            // foreach ($approvedReimbursements as $reimbursement) {
            //     $payrollItems[] = [
            //         'type' => 'reimbursement',
            //         'description' => 'Reimbursement - ' . $reimbursement->title, 
            //         'amount' => $reimbursement->amount, 
            //         'is_taxable' => $reimbursement->is_taxable ?? false // Assuming a reimbursement might have a taxable flag, default to false
            //     ];
            // }

            //         'amount' => $reimbursement->approved_amount,
            //         'is_taxable' => $reimbursement->is_taxable, // Assuming Reimbursement model has this
            //         'related_type' => get_class($reimbursement),
            //         'related_id' => $reimbursement->id
            //     ];
            //     if ($reimbursement->is_taxable) {
            //         $totalEarnings += $reimbursement->approved_amount; // Taxable reimbursements add to gross for tax calc
            //     } else {
            //         // Non-taxable reimbursements are typically paid out separately or added post-tax
            //         // For simplicity here, we'll add to earnings, but a more robust system might handle differently
            //         $totalEarnings += $reimbursement->approved_amount;
            //     }
            // }
            
            // 5. Add other Earning/Allowance items (e.g., from employee's salary structure or company policy)
            // Example: $fixedAllowance = $this->getFixedAllowances($employee);
            // if ($fixedAllowance > 0) {
            //     $payrollItems[] = ['type' => 'earning', 'description' => 'Fixed Allowance', 'amount' => $fixedAllowance, 'is_taxable' => true];
            //     $totalEarnings += $fixedAllowance;
            // }

            // 6. Calculate Statutory Deductions (e.g., Tax, Social Security)
            // This is highly country/region specific and complex. Placeholder for now.
            // $taxAmount = $this->calculateTax($totalEarnings, $employee); // Pass relevant info
            // if ($taxAmount > 0) {
            //     $payrollItems[] = ['type' => 'statutory_contribution', 'description' => 'Income Tax (PAYE)', 'amount' => $taxAmount, 'is_taxable' => false];
            //     $totalDeductions += $taxAmount;
            // }
            // $socialSecurity = $this->calculateSocialSecurity($totalEarnings, $employee);
            // if ($socialSecurity > 0) {
            //     $payrollItems[] = ['type' => 'statutory_contribution', 'description' => 'Social Security', 'amount' => $socialSecurity, 'is_taxable' => false];
            //     $totalDeductions += $socialSecurity;
            // }

            // 7. Fetch and Add Loan Repayments
            // $loanRepayments = $this->getLoanRepaymentsDue($employee, $payPeriodEnd);
            // foreach ($loanRepayments as $repayment) {
            //     $payrollItems[] = [
            //         'type' => 'loan_repayment',
            //         'description' => 'Loan Repayment - ' . $repayment->loan->loan_type, // e.g. Personal Loan
            //         'amount' => $repayment->amount_due,
            //         'is_taxable' => false,
            //         'related_type' => get_class($repayment->loan), // Or the LoanInstallment model
            //         'related_id' => $repayment->loan->id
            //     ];
            //     $totalDeductions += $repayment->amount_due;

            // Data snapshot for auditing - ensure $salaryDetails is included
            $dataSnapshot = [
                'employee_salary_details' => $salaryDetails->toArray(), // Full salary record used
                'options_passed' => $options,
                // Add other relevant data as integration proceeds (attendance, leave summaries etc.)
            ];

            $payroll = Payroll::create([
                'processing_date' => Carbon::now(),
                'pay_period_start' => $payPeriodStart->toDateString(),
                'pay_period_end' => $payPeriodEnd->toDateString(),
                'employee_id' => $employee->id,
                'company_id' => $company->id,
                'gross_salary' => $this->calculateGrossSalary($payrollItems),
                'total_deductions' => $this->calculateTotalDeductions($payrollItems),
                'net_salary' => $this->calculateNetSalary($payrollItems),
                'currency' => $salaryDetails->currency ?? $company->default_currency ?? config('app.default_currency', 'USD'),
                'status' => 'pending', // Initial status
                'payment_method' => $options['payment_method'] ?? $salaryDetails->payment_method ?? 'bank_transfer',
                'notes' => $options['notes'] ?? null,
                'processed_by' => Auth::id(),
                'data_snapshot' => json_encode($dataSnapshot),
            ]);

            foreach ($payrollItems as $itemData) {
                $payroll->items()->create($itemData);
            }

            // Log key payroll figures before commit
        // Log key payroll figures before commit
        $finalGrossSalary = $payroll->gross_salary; // Assuming these are calculated and set on $payroll object
        $finalTotalDeductions = $payroll->total_deductions;
        $finalNetSalary = $payroll->net_salary;
        Log::info("PayrollService: Final payroll figures for Employee ID: {$employee->id}", [
            'employee_id' => $employee->id,
            'payroll_id' => $payroll->id,
            'gross_salary' => $finalGrossSalary,
            'total_deductions' => $finalTotalDeductions,
            'net_salary' => $finalNetSalary,
            'status' => $payroll->status
        ]);

        DB::commit();
        return $payroll;

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error: Log::error("Payroll generation failed for employee {$employee->id}: " . $e->getMessage());
            throw $e; // Re-throw for controller to handle
        }
    }

    /**
     * Retrieves the current salary details for a given employee.
     *
     * @param Employee $employee
     * @return EmployeeSalary
     * @throws \Exception If no current salary is found for the employee.
     */
    protected function getEmployeeSalaryDetails(Employee $employee): EmployeeSalary
    {
        $currentSalary = $employee->currentSalary; // Uses the currentSalary relationship on Employee model

        if (!$currentSalary) {
            Log::error("Active salary details not found for employee ID: {$employee->id}. Payroll cannot be generated.");
            throw new \Exception("Active salary details not found for employee ID: {$employee->id}. Payroll cannot be generated.");
        }
        
        // Optionally, add checks here if the salary's effective_from/effective_to dates
        // align with the pay period, though 'is_current' should handle the primary case.
        // For payroll generation, we typically use the salary active at the point of generation
        // or as per company policy for mid-period changes.

        return $currentSalary;
    }

    protected function getAttendanceDataForPeriod(Employee $employee, Carbon $payPeriodStart, Carbon $payPeriodEnd): \Illuminate\Database\Eloquent\Collection
    {
        return Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$payPeriodStart->toDateString(), $payPeriodEnd->toDateString()])
            ->with('shift') // Eager load shift details
            ->orderBy('date', 'asc')
            ->orderBy('check_in', 'asc')
            ->get();
    }

    protected function calculateOvertimePay(Employee $employee, \Illuminate\Database\Eloquent\Collection $attendanceData, EmployeeSalary $salaryDetails, ?object $attendanceSettings): float
    {
        $totalOvertimePay = 0.0;
        $overtimeRateMultiplier = config('payroll.overtime_rate_multiplier', 1.5);

        foreach ($attendanceData as $attendance) {
            if (!$attendance->check_in || !$attendance->check_out || !$attendance->shift) {
                continue;
            }

            $hourlyRate = $this->_calculateHourlyRate($salaryDetails, $attendance->shift);
            if ($hourlyRate <= 0) continue;

            $checkInTime = Carbon::parse($attendance->check_in);
            $checkOutTime = Carbon::parse($attendance->check_out);

            $actualWorkedMinutes = $checkInTime->diffInMinutes($checkOutTime);
            $scheduledShiftMinutes = $attendance->shift->duration_in_hours * 60;
            
            $breakMinutes = 0;
            if ($attendance->shift->has_break && $attendance->shift->break_start && $attendance->shift->break_end) {
                $shiftBreakStart = Carbon::parse($attendance->date->toDateString() . ' ' . $attendance->shift->break_start->format('H:i:s'));
                $shiftBreakEnd = Carbon::parse($attendance->date->toDateString() . ' ' . $attendance->shift->break_end->format('H:i:s'));
                
                if ($checkInTime->lt($shiftBreakEnd) && $checkOutTime->gt($shiftBreakStart)) {
                    $effectiveBreakStart = $checkInTime->gt($shiftBreakStart) ? $checkInTime : $shiftBreakStart;
                    $effectiveBreakEnd = $checkOutTime->lt($shiftBreakEnd) ? $checkOutTime : $shiftBreakEnd;
                    if ($effectiveBreakEnd->gt($effectiveBreakStart)) {
                         $breakMinutes = $effectiveBreakStart->diffInMinutes($effectiveBreakEnd);
                    }
                }
            }
            $netWorkedMinutes = $actualWorkedMinutes - $breakMinutes;

            if ($netWorkedMinutes > $scheduledShiftMinutes) {
                $overtimeMinutes = $netWorkedMinutes - $scheduledShiftMinutes;
                $overtimeHours = $overtimeMinutes / 60.0;
                $totalOvertimePay += ($overtimeHours * $hourlyRate * $overtimeRateMultiplier);
            }
        }
        return round($totalOvertimePay, 2);
    }

    protected function calculateLatenessDeduction(Employee $employee, \Illuminate\Database\Eloquent\Collection $attendanceData, EmployeeSalary $salaryDetails, ?object $attendanceSettings): float
    {
        $totalLatenessDeduction = 0.0;

        foreach ($attendanceData as $attendance) {
            if (!$attendance->check_in || !$attendance->shift || !$attendance->shift->start_time) {
                continue;
            }
            
            $hourlyRate = $this->_calculateHourlyRate($salaryDetails, $attendance->shift);
            if ($hourlyRate <= 0) continue;

            $checkInTime = Carbon::parse($attendance->check_in);
            $shiftStartTimeOnDate = Carbon::parse($attendance->date->toDateString() . ' ' . $attendance->shift->start_time->format('H:i:s'));
            
            $gracePeriodMinutes = $attendance->shift->grace_period_minutes ?? ($attendanceSettings && isset($attendanceSettings->grace_period) ? $this->attendanceService->parseGracePeriodToMinutes($attendanceSettings->grace_period) : 0);
            $allowedArrivalTime = $shiftStartTimeOnDate->copy()->addMinutes($gracePeriodMinutes);

            if ($checkInTime->gt($allowedArrivalTime)) {
                $lateMinutes = $checkInTime->diffInMinutes($allowedArrivalTime);
                $lateHours = $lateMinutes / 60.0;
                $totalLatenessDeduction += ($lateHours * $hourlyRate);
            }
        }
        return round($totalLatenessDeduction, 2);
    }

private function _calculateHourlyRate(EmployeeSalary $salaryDetails, Shift $shift = null): float
{
    if (!$salaryDetails || $salaryDetails->basic_salary <= 0) {
        return 0.0;
    }

    $monthlyWorkingDays = config('payroll.default_monthly_working_days', 22);
    $dailyWorkHours = $shift ? $shift->duration_in_hours : config('payroll.default_daily_work_hours', 8);

    if ($dailyWorkHours <= 0) {
        $dailyWorkHours = config('payroll.default_daily_work_hours', 8); 
    }
    if ($dailyWorkHours <= 0) return 0.0;

    $monthlyWorkHours = $monthlyWorkingDays * $dailyWorkHours;
    if ($monthlyWorkHours <= 0) {
        return 0.0;
    }

    return round($salaryDetails->basic_salary / $monthlyWorkHours, 2);
}

protected function calculateUnpaidLeaveDeduction(Employee $employee, float $unpaidLeaveDays, EmployeeSalary $salaryDetails, \Carbon\Carbon $payPeriodDate): float
{
    if ($unpaidLeaveDays <= 0) {
        Log::debug("PayrollService: calculateUnpaidLeaveDeduction - No unpaid days for Employee ID: {$employee->id}. Deduction is 0.");
        return 0.0;
    }

    if (!$salaryDetails || $salaryDetails->basic_salary <= 0) {
        Log::warning("PayrollService: calculateUnpaidLeaveDeduction - Basic salary is zero or not set for Employee ID: {$employee->id}. Deduction is 0.");
        return 0.0;
    }

    // Use the number of days in the month of the pay period for calculation
    // This makes the per-day rate consistent for that month.
    $daysInMonth = $payPeriodDate->daysInMonth;
    // dd($daysInMonth);
    if ($daysInMonth <= 0) { // Should not happen with Carbon
        Log::error("PayrollService: calculateUnpaidLeaveDeduction - Invalid daysInMonth ({$daysInMonth}) for Employee ID: {$employee->id} for date {$payPeriodDate->toDateString()}.");
        return 0.0; // Or throw an exception
    }

    $perDayPay = $salaryDetails->basic_salary / $daysInMonth;
    $deduction = $unpaidLeaveDays * $perDayPay;

    Log::info("PayrollService: Calculating Unpaid Leave Deduction for Employee ID: {$employee->id}", [
        'employee_id' => $employee->id,
        'unpaid_leave_days' => $unpaidLeaveDays,
        'basic_salary' => $salaryDetails->basic_salary,
        'days_in_pay_period_month' => $daysInMonth,
        'per_day_pay' => round($perDayPay, 2),
        'calculated_deduction' => round($deduction, 2)
    ]);

    return round($deduction, 2);
}

protected function getApprovedReimbursementsForPeriod(Employee $employee, \Carbon\Carbon $payPeriodStart, \Carbon\Carbon $payPeriodEnd): \Illuminate\Database\Eloquent\Collection
{
    return Reimbursement::where('employee_id', $employee->id)
        ->adminApproved() // Assumes a scopeAdminApproved() exists on Reimbursement model
        ->whereBetween('admin_approved_at', [$payPeriodStart->startOfDay(), $payPeriodEnd->endOfDay()])
        ->get();
}

    /**
     * Calculate deductions for deductible leave types
     *
     * @param Employee $employee
     * @param Carbon $payPeriodStart
     * @param Carbon $payPeriodEnd
     * @param array $payrollItems Reference to the payroll items array to add deductions to
     * @return float Total deduction amount
     */
    protected function calculateDeductibleLeaveDeductions(Employee $employee, Carbon $payPeriodStart, Carbon $payPeriodEnd, array &$payrollItems): float
    {
        Log::info('Starting calculateDeductibleLeaveDeductions', [
            'employee_id' => $employee->id,
            'pay_period' => $payPeriodStart->format('Y-m-d') . ' to ' . $payPeriodEnd->format('Y-m-d')
        ]);

        // Get payroll settings for the company
        $payrollSettings = PayrollSetting::where('company_id', $employee->company_id)->first();
        
        // If no deductible leave types are set, return 0
        if (!$payrollSettings || empty($payrollSettings->deductible_leave_type_ids)) {
            \Log::info('No deductible leave types configured', ['company_id' => $employee->company_id]);
            return 0.00;
        }

        // Get the employee's salary details
        $salaryDetails = $this->getEmployeeSalaryDetails($employee);
        $monthlySalary = $salaryDetails->basic_salary;
        
        \Log::info('Employee salary details', [
            'employee_id' => $employee->id,
            'basic_salary' => $monthlySalary,
            'salary_details' => $salaryDetails->toArray()
        ]);
        
        if ($monthlySalary <= 0) {
            \Log::warning('Invalid salary amount', [
                'employee_id' => $employee->id,
                'monthly_salary' => $monthlySalary
            ]);
            return 0.00;
        }

        // Get days in month from payroll settings, default to 30 if not set
        $daysInMonth = $payrollSettings->days_in_month ?? 30;
        
        // Ensure days_in_month is at least 1 to avoid division by zero
        $daysInMonth = max(1, (int)$daysInMonth);
        
        // Calculate daily rate (monthly salary / configured days in month)
        $dailyRate = $monthlySalary / $daysInMonth;
        
        \Log::info('Daily rate calculation', [
            'monthly_salary' => $monthlySalary,
            'days_in_month' => $daysInMonth,
            'daily_rate' => $dailyRate
        ]);

        // Get all approved leave requests for the employee in the pay period
        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereIn('leave_type_id', $payrollSettings->deductible_leave_type_ids)
            ->where(function($query) use ($payPeriodStart, $payPeriodEnd) {
                $query->whereBetween('start_date', [$payPeriodStart, $payPeriodEnd])
                      ->orWhereBetween('end_date', [$payPeriodStart, $payPeriodEnd])
                      ->orWhere(function($q) use ($payPeriodStart, $payPeriodEnd) {
                          $q->where('start_date', '<=', $payPeriodStart)
                            ->where('end_date', '>=', $payPeriodEnd);
                      });
            })
            ->with('leaveType')
            ->get();

        \Log::info('Leave requests found', [
            'count' => $leaveRequests->count(),
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

        $totalDeduction = 0.00;
        $processedLeaveTypes = [];

        foreach ($leaveRequests as $leaveRequest) {
            // Skip if we've already processed this leave type
            if (in_array($leaveRequest->leave_type_id, $processedLeaveTypes)) {
                \Log::debug('Skipping duplicate leave type', [
                    'leave_request_id' => $leaveRequest->id,
                    'leave_type_id' => $leaveRequest->leave_type_id
                ]);
                continue;
            }

            // Parse dates as Carbon instances in the application timezone
            $timezone = config('app.timezone');
            $leaveStart = Carbon::parse($leaveRequest->start_date, 'UTC')
                ->setTimezone($timezone)
                ->startOfDay();
            $leaveEnd = Carbon::parse($leaveRequest->end_date, 'UTC')
                ->setTimezone($timezone)
                ->endOfDay();
            
            // Log the parsed dates for debugging
            \Log::debug('Parsed leave dates', [
                'leave_request_id' => $leaveRequest->id,
                'original_start' => $leaveRequest->start_date,
                'original_end' => $leaveRequest->end_date,
                'parsed_start' => $leaveStart->format('Y-m-d H:i:s'),
                'parsed_end' => $leaveEnd->format('Y-m-d H:i:s'),
                'timezone' => $timezone
            ]);
            
            // Adjust pay period dates to start/end of day in the same timezone
            $periodStart = Carbon::parse($payPeriodStart->format('Y-m-d'), $timezone)->startOfDay();
            $periodEnd = Carbon::parse($payPeriodEnd->format('Y-m-d'), $timezone)->endOfDay();
            
            // Skip if leave is completely outside pay period
            if ($leaveEnd->lt($periodStart) || $leaveStart->gt($periodEnd)) {
                \Log::debug('No overlap between leave and pay period', [
                    'leave_request_id' => $leaveRequest->id,
                    'leave_start' => $leaveStart->format('Y-m-d'),
                    'leave_end' => $leaveEnd->format('Y-m-d'),
                    'pay_period_start' => $periodStart->format('Y-m-d'),
                    'pay_period_end' => $periodEnd->format('Y-m-d')
                ]);
                continue;
            }

            // Calculate the overlapping period
            $start = $leaveStart->max($periodStart);
            $end = $leaveEnd->min($periodEnd);
            
            // Reset time to start of day for day counting
            $start = $start->copy()->startOfDay();
            $end = $end->copy()->startOfDay();
            
            // Calculate number of days (inclusive of both start and end dates)
            $days = $start->diffInDays($end) + 1;
            
            // Ensure days is not negative (sanity check)
            $days = max(0, $days);
            
            // Log the raw dates for debugging
            \Log::debug('Processing leave request dates', [
                'leave_request_id' => $leaveRequest->id,
                'original_start' => $leaveRequest->start_date,
                'original_end' => $leaveRequest->end_date,
                'adjusted_start' => $start->format('Y-m-d'),
                'adjusted_end' => $end->format('Y-m-d'),
                'days_calculated' => $days
            ]);
            
            // Calculate deduction amount (ensure it's positive)
            $deduction = abs($dailyRate * $days);
            $totalDeduction += $deduction;

            \Log::info('Processing leave deduction', [
                'leave_request_id' => $leaveRequest->id,
                'leave_type' => $leaveRequest->leaveType->name ?? 'Unknown',
                'leave_days' => $days,
                'daily_rate' => $dailyRate,
                'deduction_amount' => $deduction,
                'total_deduction_so_far' => $totalDeduction,
                'date_range' => $start->format('Y-m-d') . ' to ' . $end->format('Y-m-d'),
                'debug_info' => [
                    'start_date' => $start->format('Y-m-d H:i:s'),
                    'end_date' => $end->format('Y-m-d H:i:s'),
                    'days_between' => $start->diffInDays($end) + 1,
                    'is_weekend_start' => in_array($start->dayOfWeek, [0, 6]) ? 'Yes' : 'No',
                    'is_weekend_end' => in_array($end->dayOfWeek, [0, 6]) ? 'Yes' : 'No'
                ]
            ]);

            // Add to payroll items as a positive deduction
            $payrollItems[] = [
                'type' => 'deduction',
                'description' => 'Leave Deduction: ' . ($leaveRequest->leaveType->name ?? 'Leave'),
                'amount' => $deduction,
                'is_taxable' => false, // Typically leave deductions are not taxable
                'related_type' => 'App\\Models\\LeaveRequest',
                'related_id' => $leaveRequest->id,
            ];

            // Mark this leave type as processed
            $processedLeaveTypes[] = $leaveRequest->leave_type_id;
        }

        // Ensure final deduction is positive and rounded
        $finalDeduction = round($totalDeduction, 2);
        \Log::info('Final leave deduction calculation', [
            'employee_id' => $employee->id,
            'total_deduction' => $finalDeduction,
            'processed_leave_types' => $processedLeaveTypes
        ]);

        return $finalDeduction;
    }

    /**
     * Calculate net unpaid days by correlating attendance with approved leave requests.
     *
     * @param Employee $employee
     * @param Carbon $payPeriodStart
     * @param Carbon $payPeriodEnd
     * @param \Illuminate\Database\Eloquent\Collection $attendanceDataForPeriod
     * @param \Illuminate\Database\Eloquent\Collection $approvedLeaveRequestsInPeriod
     * @param AttendanceService $attendanceService
     * @return array
     */
    public function calculateAttendanceBasedUnpaidDays(
        Employee $employee,
        Carbon $payPeriodStart,
        Carbon $payPeriodEnd,
        $attendanceData, // Collection of Attendance records
        $approvedLeaveRequests, // Collection of LeaveRequest records
        AttendanceService $attendanceService // Already injected
    ): array // Changed return type
    {
        Log::info("PayrollService: Starting calculateAttendanceBasedUnpaidDays for Employee ID: {$employee->id}", [
            'employee_id' => $employee->id,
            'pay_period_start' => $payPeriodStart->toDateString(),
            'pay_period_end' => $payPeriodEnd->toDateString(),
            'attendance_data_count' => $attendanceData->count(),
            'approved_leave_requests_count' => $approvedLeaveRequests->count()
        ]);

        $totalAbsentDaysPortion = 0.0;
        $totalUnpaidLeaveDaysPortion = 0.0;
        $lateDaysCount = 0;
        
        // Get the attendance settings for the employee's company
        $attendanceSettings = $this->attendanceService->getAttendanceSettings($employee->company_id);
        $companyWeekendDays = $attendanceSettings->weekend_days ?? ['Sunday', 'Saturday'];
        
        // Get payroll settings for late arrival threshold and deduction rate
        $payrollSettings = PayrollSetting::where('company_id', $employee->company_id)->first();
        $lateArrivalThreshold = $payrollSettings->late_arrival_threshold ?? 0;
        $lateDeductionRate = $payrollSettings->late_arrival_deduction_days ?? 0.25;
        
        // Convert day names to numeric values (0=Sunday, 6=Saturday)
        $dayMap = ['sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6];
        $companyWeekendDaysNumeric = array_map(function($day) use ($dayMap) {
            return $dayMap[strtolower(trim($day))] ?? -1;
        }, (array)$companyWeekendDays);

        $companyId = $employee->company_id;
        // Fetch attendance settings which include weekend days
        $attendanceSettings = $this->attendanceService->getAttendanceSettings($companyId);
        // Weekend days are numeric (0 for Sunday, ..., 6 for Saturday)
        // Default to an empty array if settings or weekend_days are not found/valid
        $companyWeekendDaysNumeric = ($attendanceSettings && isset($attendanceSettings->weekend_days) && is_array($attendanceSettings->weekend_days)) 
                                     ? $attendanceSettings->weekend_days 
                                     : [];

        // $companyHolidays will be checked day by day using isHoliday()

        $period = new \DatePeriod($payPeriodStart, new \DateInterval('P1D'), $payPeriodEnd);

        foreach ($period as $date) {
            $currentDate = Carbon::instance($date);
            $dateString = $currentDate->toDateString();
            
            // Carbon's dayOfWeek: 0 for Sunday, 1 for Monday, ..., 6 for Saturday
            $currentDayOfWeekNumeric = $currentDate->dayOfWeek; 
            $isCompanyWeekendDay = in_array($currentDayOfWeekNumeric, $companyWeekendDaysNumeric);
            // Check if the current day is a holiday by calling isHoliday() from AttendanceService
            $isCompanyHoliday = $this->attendanceService->isHoliday($currentDate);

            $dayLogPrefix = "PayrollService: Unpaid calc for {$employee->id} on {$dateString}:";
            $dayPortionLog = ['Att' => 'None', 'Leave' => 'None'];
            $currentDayAbsentPortion = 0.0;
            $currentDayUnpaidLeavePortion = 0.0;

            if ($isCompanyWeekendDay || $isCompanyHoliday) {
                Log::debug("{$dayLogPrefix} Portion: 0 (Non-workday - " . ($isCompanyWeekendDay ? "Weekend" : "Holiday") . ")");
                continue; // Skip weekends and holidays
            }
            // If not skipped, it's a workday.

            $attendanceRecord = $attendanceData->first(function ($att) use ($dateString) {
                return Carbon::parse($att->date)->toDateString() === $dateString;
            });

            $approvedLeave = $approvedLeaveRequests->first(function ($lr) use ($currentDate) {
                $leaveStartDate = Carbon::parse($lr->start_date);
                $leaveEndDate = Carbon::parse($lr->end_date);
                return $currentDate->betweenIncluded($leaveStartDate, $leaveEndDate);
            });

            if ($approvedLeave) {
                $leaveTypeName = $approvedLeave->leaveType->name ?? 'Unknown Leave';
                // IMPORTANT: Assumes LeaveType model has a boolean 'is_paid' attribute.
                // If not, logic to determine if leave is paid/unpaid needs to be based on name or other criteria.
                $isPaidLeave = $approvedLeave->leaveType->is_paid ?? false; 
                $dayPortionLog['Leave'] = $leaveTypeName . ($isPaidLeave ? " (Paid)" : " (Unpaid)");

                if ($approvedLeave->duration_type === 'full_day') {
                    if (!$isPaidLeave) {
                        $currentDayUnpaidLeavePortion = 1.0;
                    }
                    // Full day covered by leave (either paid or unpaid), so no absent portion from attendance.
                    $dayPortionLog['Att'] = $attendanceRecord ? $attendanceRecord->status : 'On Full Day Leave';
                } elseif ($approvedLeave->duration_type === 'half_day') {
                    $leavePortionCovered = 0.5;
                    if (!$isPaidLeave) {
                        $currentDayUnpaidLeavePortion = 0.5;
                    }
                    // Assess the other half of the day based on attendance
                    if (!$attendanceRecord || $attendanceRecord->status === 'Absent') {
                        $currentDayAbsentPortion = 0.5; // Other half absent
                        $dayPortionLog['Att'] = $attendanceRecord ? $attendanceRecord->status . ' (Other Half)' : 'None (Other Half)';
                    } elseif ($attendanceRecord->status === 'Half Day') {
                        // Worked other half, or attendance also shows half day (consistent)
                        $dayPortionLog['Att'] = 'Half Day (Worked Other Half)';
                    } elseif ($attendanceRecord->status === 'Present' || $attendanceRecord->status === 'Late') {
                        // Present for the full day despite half-day leave? This implies other half worked.
                        $dayPortionLog['Att'] = $attendanceRecord->status . ' (Implies Other Half Worked)';
                    } else {
                        $dayPortionLog['Att'] = $attendanceRecord ? $attendanceRecord->status : 'On Half Day Leave';
                    }
                }
            } else { // No approved leave covers the day
                $dayPortionLog['Leave'] = 'None';
                if (!$attendanceRecord) {
                    $currentDayAbsentPortion = 1.0; // Absent (no record)
                    $dayPortionLog['Att'] = 'None';
                } elseif ($attendanceRecord->status === 'Absent') {
                    $currentDayAbsentPortion = 1.0; // Explicitly Absent
                    $dayPortionLog['Att'] = 'Absent';
                } elseif ($attendanceRecord->status === 'Half Day') {
                    $currentDayAbsentPortion = 0.5; // Worked half, absent other half
                    $dayPortionLog['Att'] = 'Half Day';
                } elseif ($attendanceRecord->status === 'On Leave') {
                    $currentDayAbsentPortion = 1.0; // Marked 'On Leave' in attendance, but no approved HR leave
                    $dayPortionLog['Att'] = 'On Leave (Unapproved)';
                } elseif ($attendanceRecord->status === 'Late') {
                    $lateDaysCount++;
                    $currentDayAbsentPortion = 0.0;
                    
                    // Check if we're past the threshold for late days
                    if ($lateDaysCount > $lateArrivalThreshold) {
                        // Apply the late deduction rate for this day
                        $lateDeductionPortion = min(1.0, (float)$lateDeductionRate);
                        $currentDayAbsentPortion = $lateDeductionPortion;
                        $dayPortionLog['Att'] = 'Late (Deduction: ' . ($lateDeductionPortion * 100) . '%, Late Day #' . $lateDaysCount . ')';
                    } else {
                        // Within threshold, no deduction
                        $dayPortionLog['Att'] = 'Late (No Deduction, Late Day #' . $lateDaysCount . ' of ' . $lateArrivalThreshold . ')';
                    }
                } else { // Present, etc.
                    $currentDayAbsentPortion = 0.0;
                    $dayPortionLog['Att'] = $attendanceRecord->status;
                }
            }
            
            $totalUnpaidLeaveDaysPortion += $currentDayUnpaidLeavePortion;
            $totalAbsentDaysPortion += $currentDayAbsentPortion;

            Log::debug("{$dayLogPrefix} UnpaidLeaveP: {$currentDayUnpaidLeavePortion}, AbsentP: {$currentDayAbsentPortion}", $dayPortionLog);
        }

        Log::info("PayrollService: Finished calculateAttendanceBasedUnpaidDays for Employee ID: {$employee->id}", [
            'employee_id' => $employee->id,
            'total_calculated_unpaid_leave_days_portion' => $totalUnpaidLeaveDaysPortion,
            'total_calculated_absent_days_portion' => $totalAbsentDaysPortion,
        ]);

        return [
            'unpaid_leave_days_portion' => $totalUnpaidLeaveDaysPortion,
            'absent_days_portion' => $totalAbsentDaysPortion,
        ];
    }

    public function calculateGrossSalary(array $payrollItems): float
    {
        $grossSalary = 0.0;
        foreach ($payrollItems as $item) {
            if (in_array($item['type'], ['earning', 'reimbursement', 'bonus', 'overtime'])) {
                $grossSalary += (float)$item['amount'];
            }
        }
        return round($grossSalary, 2);
    }

    /**
     * Calculate total deductions based on payroll items.
     */
    public function calculateTotalDeductions(array $payrollItems): float
    {
        $totalDeductions = 0.0;
        foreach ($payrollItems as $item) {
            if ($item['type'] === 'deduction') {
                $totalDeductions += (float)$item['amount'];
            }
        }
        return round($totalDeductions, 2);
    }

    /**
     * Calculate net salary based on payroll items.
     */
    public function calculateNetSalary(array $payrollItems): float
    {
        $grossSalary = $this->calculateGrossSalary($payrollItems);
        $totalDeductions = $this->calculateTotalDeductions($payrollItems);
        
        // Ensure we don't go below zero
        $netSalary = max(0, $grossSalary - $totalDeductions);
        
        \Log::info('Net salary calculation', [
            'gross_salary' => $grossSalary,
            'total_deductions' => $totalDeductions,
            'net_salary' => $netSalary
        ]);
        
        return round($netSalary, 2);
    }

    // We will add more methods here for:
    // - Fetching salary details
    // - Calculating attendance-based pay
    // - Processing leaves
    // - Adding reimbursements
    // - Handling statutory deductions
    // - etc.

    private function _processBeneficiaryBadges(
        Employee $employee,
        Carbon $payPeriodStart,
        Carbon $payPeriodEnd,
        float $ctcMonthly,
        float $basicSalaryMonthly,
        array &$payrollItems
    ): void {
        Log::debug("PayrollService: Starting _processBeneficiaryBadges for Employee ID: {$employee->id}", [
            'ctc_monthly' => $ctcMonthly,
            'basic_salary_monthly' => $basicSalaryMonthly
        ]);

        // Get employee's assigned badges
        $assignedBadgesData = $employee->assignedBeneficiaryBadges()
            ->where('is_applicable', true)
            ->where(function ($query) use ($payPeriodStart, $payPeriodEnd) {
                // Badge is active if its period overlaps with the pay period
                $query->where(function ($q) use ($payPeriodEnd) { // Start date condition
                    $q->whereNull('start_date')->orWhere('start_date', '<=', $payPeriodEnd->toDateString());
                })
                ->where(function ($q) use ($payPeriodStart) { // End date condition
                    $q->whereNull('end_date')->orWhere('end_date', '>=' , $payPeriodStart->toDateString());
                });
            })
            ->get();
            
        // Get company-wide badges that are active and not already assigned to the employee
        $companyWideBadges = BeneficiaryBadge::where('company_id', $employee->company_id)
            ->where('is_company_wide', true)
            ->where('is_active', true)
            ->whereNotIn('id', $assignedBadgesData->pluck('beneficiary_badge_id')->toArray())
            ->get()
            ->map(function($badge) {
                return new EmployeeBeneficiaryBadge([
                    'beneficiary_badge_id' => $badge->id,
                    'employee_id' => null, // Will be set when processing
                    'custom_value' => $badge->value,
                    'custom_calculation_type' => $badge->calculation_type,
                    'custom_based_on' => $badge->based_on,
                    'is_applicable' => true,
                    'start_date' => null,
                    'end_date' => null,
                    'beneficiaryBadge' => $badge
                ]);
            });
            
        // Merge assigned badges with company-wide badges
        $allBadges = $assignedBadgesData->merge($companyWideBadges);

        $activeBadgesConfigs = [];
        foreach ($allBadges as $assignedBadge) {
            $badge = $assignedBadge->beneficiaryBadge ?? $assignedBadge->beneficiaryBadge()->first();
            
            if ($badge && $badge->is_active) {
                $config = [
                    'name' => $badge->name,
                    'value' => $badge->value, // Always use the latest value from the badge
                    'calculation_type' => $assignedBadge->custom_calculation_type ?? $badge->calculation_type,
                    'original_type' => $badge->type, // 'allowance' or 'deduction'
                    'beneficiary_badge_id' => $badge->id,
                    'based_on' => null, // Determined next based on calculation_type
                ];
                
                // Log the values for debugging
                Log::debug("Processing badge: {$badge->name}", [
                    'badge_value' => $badge->value,
                    'assigned_value' => $assignedBadge->custom_value ?? 'null',
                    'calculation_type' => $config['calculation_type'],
                    'original_type' => $config['original_type']
                ]);

                if ($config['calculation_type'] === 'percentage') {
                    if ($assignedBadge->custom_calculation_type) { // Custom calculation type is percentage
                        $config['based_on'] = $assignedBadge->custom_based_on ?: $badge->based_on;
                    } else { // Default calculation type is percentage
                        $config['based_on'] = $badge->based_on;
                    }
                    if (empty($config['based_on'])) {
                        Log::warning("PayrollService: Percentage badge '{$badge->name}' (ID: {$badge->id}) for employee {$employee->id} has no 'based_on' value. Skipping.", ['badge_config' => $config]);
                        continue;
                    }
                }
                $activeBadgesConfigs[] = $config;
            }
        }

        $allowances = array_filter($activeBadgesConfigs, fn($c) => $c['original_type'] === 'allowance');
        $deductions = array_filter($activeBadgesConfigs, fn($c) => $c['original_type'] === 'deduction');

        // Process Flat Allowances
        foreach ($allowances as $allowance) {
            if ($allowance['calculation_type'] === 'flat') {
                $payrollItems[] = [
                    'type' => 'earning',
                    'description' => $allowance['name'],
                    'amount' => round((float) $allowance['value'], 2),
                    'is_taxable' => true, 
                    'is_fixed' => true,
                    'meta' => ['source' => 'beneficiary_badge', 'badge_id' => $allowance['beneficiary_badge_id']]
                ];
                Log::debug("PayrollService: Added flat allowance '{$allowance['name']}'", ['amount' => $allowance['value'], 'employee_id' => $employee->id]);
            }
        }

        // Calculate current gross earnings for percentage allowances based on 'gross_earnings'
        $currentGrossForPctAllowances = 0;
        foreach ($payrollItems as $item) {
            if (in_array($item['type'], ['earning'])) { // Only 'earning' type items contribute to this base
                $currentGrossForPctAllowances += (float)$item['amount'];
            }
        }
        Log::debug("PayrollService: Gross for Pct Allowances base calculation for Employee ID: {$employee->id}", ['amount' => $currentGrossForPctAllowances]);

        // Process Percentage Allowances
        foreach ($allowances as $allowance) {
            if ($allowance['calculation_type'] === 'percentage') {
                $baseAmount = 0;
                $basedOnField = strtolower($allowance['based_on'] ?? '');
                switch ($basedOnField) {
                    case 'ctc':
                        $baseAmount = $ctcMonthly;
                        break;
                    case 'basic_salary':
                        $baseAmount = $basicSalaryMonthly;
                        break;
                    case 'gross_earnings':
                        $baseAmount = $currentGrossForPctAllowances;
                        break;
                    default:
                        Log::warning("PayrollService: Unknown 'based_on' value '{$allowance['based_on']}' for percentage allowance '{$allowance['name']}'. Skipping.", ['employee_id' => $employee->id]);
                        continue 2;
                }
                $calculatedAmount = ($allowance['value'] / 100) * $baseAmount;
                $payrollItems[] = [
                    'type' => 'earning',
                    'description' => $allowance['name'],
                    'amount' => round((float) $calculatedAmount, 2),
                    'is_taxable' => true,
                    'is_fixed' => false,
                    'meta' => ['source' => 'beneficiary_badge', 'badge_id' => $allowance['beneficiary_badge_id'], 'base_amount' => $baseAmount, 'percentage' => $allowance['value'], 'based_on' => $basedOnField]
                ];
                Log::debug("PayrollService: Added percentage allowance '{$allowance['name']}' for Employee ID: {$employee->id}", ['amount' => $calculatedAmount, 'base' => $allowance['based_on'], 'base_val' => $baseAmount]);
            }
        }
        
        // Recalculate gross earnings after ALL allowances for deduction calculations
        $currentGrossForPctDeductions = 0;
        foreach ($payrollItems as $item) {
             if (in_array($item['type'], ['earning', 'reimbursement', 'bonus', 'overtime'])) { // All positive income
                $currentGrossForPctDeductions += (float)$item['amount'];
            }
        }
        Log::debug("PayrollService: Gross for Pct Deductions base calculation for Employee ID: {$employee->id}", ['amount' => $currentGrossForPctDeductions]);

        // Process Flat Deductions
        foreach ($deductions as $deduction) {
            if ($deduction['calculation_type'] === 'flat') {
                $payrollItems[] = [
                    'type' => 'deduction',
                    'description' => $deduction['name'],
                    'amount' => round((float) $deduction['value'], 2),
                    'meta' => ['source' => 'beneficiary_badge', 'badge_id' => $deduction['beneficiary_badge_id']]
                ];
                Log::debug("PayrollService: Added flat deduction '{$deduction['name']}' for Employee ID: {$employee->id}", ['amount' => $deduction['value']]);
            }
        }

        // Process Percentage Deductions
        foreach ($deductions as $deduction) {
            if ($deduction['calculation_type'] === 'percentage') {
                $baseAmount = 0;
                $basedOnField = strtolower($deduction['based_on'] ?? '');
                switch ($basedOnField) {
                    case 'ctc':
                        $baseAmount = $ctcMonthly;
                        break;
                    case 'basic_salary':
                        $baseAmount = $basicSalaryMonthly;
                        break;
                    case 'gross_earnings':
                        $baseAmount = $currentGrossForPctDeductions;
                        break;
                    default:
                        Log::warning("PayrollService: Unknown 'based_on' value '{$deduction['based_on']}' for percentage deduction '{$deduction['name']}'. Skipping.", ['employee_id' => $employee->id]);
                        continue 2;
                }
                $calculatedAmount = ($deduction['value'] / 100) * $baseAmount;
                $payrollItems[] = [
                    'type' => 'deduction',
                    'description' => $deduction['name'],
                    'amount' => round((float) $calculatedAmount, 2),
                    'meta' => ['source' => 'beneficiary_badge', 'badge_id' => $deduction['beneficiary_badge_id'], 'base_amount' => $baseAmount, 'percentage' => $deduction['value'], 'based_on' => $basedOnField]
                ];
                Log::debug("PayrollService: Added percentage deduction '{$deduction['name']}' for Employee ID: {$employee->id}", ['amount' => $calculatedAmount, 'base' => $deduction['based_on'], 'base_val' => $baseAmount]);
            }
        }
        Log::debug("PayrollService: Finished _processBeneficiaryBadges for Employee ID: {$employee->id}");
    }
}
