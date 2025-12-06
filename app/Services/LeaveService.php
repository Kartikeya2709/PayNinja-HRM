<?php

namespace App\Services;

use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;

class LeaveService
{
    /**
     * Check if employee has sufficient leave balance based on leave settings
     *
     * @param Employee $employee
     * @param LeaveType $leaveType
     * @param int $requestedDays
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param bool $isHalfDay
     * @return array
     */
    public function checkLeaveBalance(Employee $employee, LeaveType $leaveType, int $requestedDays, Carbon $startDate, Carbon $endDate, bool $isHalfDay = false)
    {
        $currentYear = $startDate->year;
        $currentMonth = $startDate->month;

        // Get or create leave balance
        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'year' => $currentYear
            ],
            [
                'total_days' => $leaveType->default_days,
                'used_days' => 0,
                'carried_over_days' => 0
            ]
        );

        // Check monthly limit if set
        if ($leaveType->monthly_limit !== null) {
            $monthlyUsedDays = $this->getMonthlyUsedDays($employee, $leaveType, $currentYear, $currentMonth);

            if ($monthlyUsedDays + $requestedDays > $leaveType->monthly_limit) {
                return [
                    'success' => false,
                    'message' => "Monthly limit exceeded. You have used {$monthlyUsedDays} out of {$leaveType->monthly_limit} days this month."
                ];
            }
        }

        // Check yearly limit if set
        if ($leaveType->yearly_limit !== null) {
            $yearlyUsedDays = $leaveBalance->used_days;

            if ($yearlyUsedDays + $requestedDays > $leaveType->yearly_limit) {
                return [
                    'success' => false,
                    'message' => "Yearly limit exceeded. You have used {$yearlyUsedDays} out of {$leaveType->yearly_limit} days this year."
                ];
            }
        }

        // Check if negative balance is allowed
        if (!$leaveType->allow_negative_balance) {
            // Use the actual remaining_days value that includes carry forward days
            $remainingDays = $leaveBalance->remaining_days;

            if ($requestedDays > $remainingDays) {
                return [
                    'success' => false,
                    'message' => "Insufficient leave balance. You have only {$remainingDays} days remaining."
                ];
            }
        }
log::info("Leave balance check passed for employee {$employee->id} - Leave Type: {$leaveType->id}, Requested Days: {$requestedDays}");
        return [
            'success' => true,
            'message' => 'Leave balance check passed.'
        ];
    }

    /**
     * Get monthly used days for a specific leave type
     *
     * @param Employee $employee
     * @param LeaveType $leaveType
     * @param int $year
     * @param int $month
     * @return int
     */
    protected function getMonthlyUsedDays(Employee $employee, LeaveType $leaveType, int $year, int $month)
    {
        log::info("Getting monthly used days for employee {$employee->id} - Leave Type: {$leaveType->id}, Year: {$year}, Month: {$month}");

        return LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->sum('total_days');
    }

    /**
     * Calculate carry forward days based on leave settings
     *
     * @param Employee $employee
     * @param LeaveType $leaveType
     * @param int $currentYear
     * @param FinancialYear|null $financialYear
     * @return int
     */
    public function calculateCarryForwardDays(Employee $employee, LeaveType $leaveType, int $currentYear, $financialYear = null)
    {
        if (!$leaveType->enable_carry_forward) {
            return 0;
        }

        // Handle different carry forward logic based on disbursement cycle
        if ($leaveType->disbursement_cycle === 'monthly') {
            // For monthly cycles, use leave_value_per_cycle instead of unused days from previous month
            $leaveValue = $leaveType->leave_value_per_cycle ?? $leaveType->default_days;

            // Apply max_carry_forward_days limit
            $maxCarryForward = $leaveType->max_carry_forward_days ?? PHP_INT_MAX;
            if ($leaveValue > $maxCarryForward) {
                $leaveValue = $maxCarryForward;
            }

            log::info("Calculated monthly leave value for employee {$employee->id} - Leave Type: {$leaveType->id}, Leave Value: {$leaveValue}");
            return $leaveValue;
        } else {
            // For yearly/quarterly/half-yearly cycles, carry forward from previous year
            $previousYear = $currentYear - 1;
            $previousYearBalance = LeaveBalance::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('year', $previousYear)
                ->first();

            if (!$previousYearBalance) {
                return 0;
            }

            $unusedDays = $previousYearBalance->total_days - $previousYearBalance->used_days;

            // Never carry forward negative days
            if ($unusedDays < 0) {
                $unusedDays = 0;
            }

            // Apply max carry forward limit if set
            if ($leaveType->max_carry_forward_days !== null && $unusedDays > $leaveType->max_carry_forward_days) {
                $unusedDays = $leaveType->max_carry_forward_days;
            }

            // Apply yearly carry forward limit if set
            if ($leaveType->yearly_carry_forward_limit > 0 && $unusedDays > $leaveType->yearly_carry_forward_limit) {
                $unusedDays = $leaveType->yearly_carry_forward_limit;
            }

            log::info("Calculated yearly carry forward days for employee {$employee->id} - Leave Type: {$leaveType->id}, Unused Days: {$unusedDays}");
            return $unusedDays;
        }
    }

    /**
     * Process leave disbursement based on cycle settings
     *
     * @param Employee $employee
     * @param LeaveType $leaveType
     * @param int|FinancialYear $year
     * @return void
     */
    public function processLeaveDisbursement(Employee $employee, LeaveType $leaveType, $year)
    {
        // Handle both int year and FinancialYear object
        if ($year instanceof \App\Models\FinancialYear) {
            $financialYear = $year;
            $year = $financialYear->start_date->year;
        } else {
            $financialYear = null;
        }

        Log::info("Processing leave disbursement for employee {$employee->id} - Leave Type: {$leaveType->name}");

        // Find existing leave balance or create new one
        $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', $year)
            ->first();

        // Calculate carry forward days if enabled
        $carryForwardDays = $this->calculateCarryForwardDays($employee, $leaveType, $year, $financialYear);
        Log::info("Calculated carry forward days: {$carryForwardDays} for employee {$employee->id}");

        if (!$leaveBalance) {
            // Create new balance record
            Log::info("Creating new leave balance for employee {$employee->id}");
            $leaveBalance = new LeaveBalance();
            $leaveBalance->employee_id = $employee->id;
            $leaveBalance->leave_type_id = $leaveType->id;
            $leaveBalance->year = $year;
            $leaveBalance->total_days = $leaveType->default_days;
            $leaveBalance->used_days = 0;
            $leaveBalance->carried_over_days = 0;
        } else {
            Log::info("Found existing balance for employee {$employee->id}: total_days={$leaveBalance->total_days}, used_days={$leaveBalance->used_days}");
        }

        // Only add or subtract from ongoing balance - never override
        if ($leaveType->disbursement_time === 'start_of_cycle') {
            // For start_of_cycle, add carry forward days to remaining_days only
            $oldRemaining = $leaveBalance->remaining_days;
            $oldTotal = $leaveBalance->total_days;

            // Apply max_carry_forward_days limit to the carry forward amount only
            $maxCarryForward = $leaveType->max_carry_forward_days ?? PHP_INT_MAX;
            $actualCarryForward = min($carryForwardDays, $maxCarryForward);

            $leaveBalance->carried_over_days = $actualCarryForward;

            // Calculate what the new remaining days should be
            $newRemaining = $oldRemaining + $actualCarryForward;

            // Ensure remaining_days doesn't exceed total_days
            if ($newRemaining > $leaveBalance->total_days) {
                $finalRemaining = $leaveBalance->total_days;
                Log::info("Added {$actualCarryForward} carry forward days to employee {$employee->id} but capped remaining_days at total_days: {$oldRemaining} -> {$finalRemaining}");
            } else {
                $finalRemaining = $newRemaining;
                Log::info("Added {$actualCarryForward} carry forward days to employee {$employee->id} remaining balance: {$oldRemaining} -> {$finalRemaining}");
            }

            // Set the final remaining_days value
            $leaveBalance->remaining_days = $finalRemaining;

            // total_days remains unchanged as per requirements
            Log::info("Employee {$employee->id} total_days remains unchanged: {$leaveBalance->total_days}");
        } else {
            // For end_of_cycle, use the same logic as start_of_cycle
            // Add carry forward days to remaining_days only
            $oldRemaining = $leaveBalance->remaining_days;
            $oldTotal = $leaveBalance->total_days;

            // Apply max_carry_forward_days limit to the carry forward amount only
            $maxCarryForward = $leaveType->max_carry_forward_days ?? PHP_INT_MAX;
            $actualCarryForward = min($carryForwardDays, $maxCarryForward);

            $leaveBalance->carried_over_days = $actualCarryForward;

            // Calculate what the new remaining days should be
            $newRemaining = $oldRemaining + $actualCarryForward;

            // Ensure remaining_days doesn't exceed total_days
            if ($newRemaining > $leaveBalance->total_days) {
                $finalRemaining = $leaveBalance->total_days;
                Log::info("Added {$actualCarryForward} carry forward days to employee {$employee->id} but capped remaining_days at total_days: {$oldRemaining} -> {$finalRemaining}");
            } else {
                $finalRemaining = $newRemaining;
                Log::info("Added {$actualCarryForward} carry forward days to employee {$employee->id} remaining balance: {$oldRemaining} -> {$finalRemaining}");
            }

            // Set the final remaining_days value
            $leaveBalance->remaining_days = $finalRemaining;

            // total_days remains unchanged as per requirements
            Log::info("Employee {$employee->id} total_days remains unchanged: {$leaveBalance->total_days}");
        }

        // Note: Removed monthly used_days reset to allow used days to accumulate
        // Monthly limits are still enforced through validation logic

        $leaveBalance->save();
        Log::info("Saved updated balance for employee {$employee->id}: total_days={$leaveBalance->total_days}, used_days={$leaveBalance->used_days}, remaining_days={$leaveBalance->remaining_days}, carried_over_days={$leaveBalance->carried_over_days}");
    }

    /**
     * Check if half day leave is allowed
     *
     * @param LeaveType $leaveType
     * @return bool
     */
    public function isHalfDayAllowed(LeaveType $leaveType)
    {
        return $leaveType->allow_half_day_leave;
    }

    /**
     * Calculate leave deduction based on half day settings
     *
     * @param LeaveType $leaveType
     * @param bool $isHalfDay
     * @param int $days
     * @return float
     */
    public function calculateLeaveDeduction(LeaveType $leaveType, bool $isHalfDay, int $days)
    {
        if ($isHalfDay) {
            return $leaveType->half_day_deduction_priority === 'half_day_first' ? 0.5 : 1.0;
        }

        return $days;
    }

    /**
     * Get leave balance summary for display
     *
     * @param Employee $employee
     * @param LeaveType $leaveType
     * @param int $year
     * @return array
     */
    public function getLeaveBalanceSummary(Employee $employee, LeaveType $leaveType, int $year)
    {
        $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', $year)
            ->first();

        if (!$leaveBalance) {
            return [
                'total_days' => 0,
                'used_days' => 0,
                'remaining_days' => 0,
                'carried_over_days' => 0,
                'monthly_used' => 0,
                'monthly_limit' => $leaveType->monthly_limit,
                'yearly_limit' => $leaveType->yearly_limit
            ];
        }

        $currentMonth = Carbon::now()->month;
        $monthlyUsed = $this->getMonthlyUsedDays($employee, $leaveType, $year, $currentMonth);

        return [
            'total_days' => $leaveBalance->total_days,
            'used_days' => $leaveBalance->used_days,
            'remaining_days' => $leaveBalance->remaining_days, // Use the stored value, not recalculated
            'carried_over_days' => $leaveBalance->carried_over_days,
            'monthly_used' => $monthlyUsed,
            'monthly_limit' => $leaveType->monthly_limit,
            'yearly_limit' => $leaveType->yearly_limit
        ];
    }
}
