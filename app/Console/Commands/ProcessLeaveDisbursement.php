<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LeaveService;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Models\FinancialYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessLeaveDisbursement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:disburse
                            {--company= : Process disbursement for specific company}
                            {--dry-run : Show what would be processed without making changes}
                            {--force : Force processing even if already processed}
                            {--date= : Process disbursement for a specific date (format: YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process leave disbursement for all employees based on their leave type settings';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting leave disbursement processing...');

        $leaveService = new LeaveService();
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Use specified date or current date
        $currentDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::now();

        // Get all active leave types
        $query = LeaveType::where('is_active', true);

        // Filter by company if specified
        if ($this->option('company')) {
            $query->where('company_id', $this->option('company'));
        }

        $leaveTypes = $query->get();

        if ($leaveTypes->isEmpty()) {
            $this->info('No active leave types found to process.');
            return 0;
        }

        $this->info("Found {$leaveTypes->count()} active leave types to process.");

        $processedCount = 0;
        $errorCount = 0;

        foreach ($leaveTypes as $leaveType) {
            try {
                // Skip leave types that don't have carry forward enabled or aren't properly configured
                if (!$leaveType->enable_carry_forward && $leaveType->disbursement_cycle !== 'monthly') {
                    $this->info("Skipping leave type {$leaveType->name} - carry forward not enabled and not monthly cycle");
                    continue;
                }

                // Get the financial year for this company
                $financialYear = FinancialYear::getActiveForCompany($leaveType->company_id);

                if (!$financialYear) {
                    $this->error("No active financial year found for company {$leaveType->company_id}. Skipping leave type {$leaveType->name}");
                    continue;
                }

                // Check if the current date falls within the financial year
                if (!$financialYear->containsDate($currentDate)) {
                    $this->info("Current date {$currentDate->format('Y-m-d')} is outside financial year {$financialYear->name} for company {$leaveType->company_id}. Skipping.");
                    continue;
                }

                // Check if this leave type needs processing based on its cycle
                if ($this->shouldProcessDisbursement($leaveType, $currentDate, $financialYear)) {
                    // Get all employees who should receive this leave type
                    $employees = Employee::where('company_id', $leaveType->company_id)
                                        ->where('is_active', true)
                                        ->get();

                    foreach ($employees as $employee) {
                        try {
                            if ($dryRun) {
                                $this->info("[DRY RUN] Would process disbursement for employee {$employee->id} ({$employee->name}) - Leave Type: {$leaveType->name}");
                                $this->info("[DRY RUN] Cycle: {$leaveType->disbursement_cycle}, Timing: {$leaveType->disbursement_time}");
                            } else {
                                $leaveService->processLeaveDisbursement($employee, $leaveType, $financialYear);
                                $this->info("Processed disbursement for employee {$employee->id} ({$employee->name}) - Leave Type: {$leaveType->name}");
                                $processedCount++;
                            }
                        } catch (\Exception $e) {
                            $this->error("Error processing employee {$employee->id}: " . $e->getMessage());
                            $errorCount++;
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error processing leave type {$leaveType->id}: " . $e->getMessage());
                $errorCount++;
            }
        }

        $this->info("Leave disbursement processing completed.");
        $this->info("Processed: {$processedCount} employees");
        $this->info("Errors: {$errorCount}");

        if ($errorCount > 0) {
            return 1;
        }

        return 0;
    }

    /**
     * Determine if a leave type should be processed based on its disbursement cycle
     *
     * @param LeaveType $leaveType
     * @param Carbon $currentDate
     * @param FinancialYear|null $financialYear
     * @return bool
     */
    protected function shouldProcessDisbursement(LeaveType $leaveType, Carbon $currentDate, $financialYear = null)
    {
        $cycle = $leaveType->disbursement_cycle;
        $timing = $leaveType->disbursement_time;

        // If we have a financial year, adjust the cycle logic to work within financial year boundaries
        if ($financialYear) {
            return $this->shouldProcessDisbursementForFinancialYear($leaveType, $currentDate, $financialYear);
        }

        // Check if we should process based on the cycle and timing (original calendar-based logic)
        switch ($cycle) {
            case 'monthly':
                // Check if it's the start or end of the month
                if ($timing === 'start_of_cycle') {
                    return $currentDate->day === 1;
                } else {
                    return $currentDate->day === $currentDate->daysInMonth;
                }

            case 'quarterly':
                // Check if it's the start or end of a quarter
                $quarter = ceil($currentDate->month / 3);
                $quarterStartMonth = (($quarter - 1) * 3) + 1;
                $quarterEndMonth = $quarter * 3;

                if ($timing === 'start_of_cycle') {
                    return $currentDate->month === $quarterStartMonth && $currentDate->day === 1;
                } else {
                    // End of quarter - last day of the quarter's last month
                    return $currentDate->month === $quarterEndMonth && $currentDate->day === $currentDate->daysInMonth;
                }

            case 'half_yearly':
                // Check if it's the start or end of a half year
                if ($timing === 'start_of_cycle') {
                    // Start of half year: January 1st or July 1st
                    return ($currentDate->month === 1 || $currentDate->month === 7) && $currentDate->day === 1;
                } else {
                    // End of half year: June 30th or December 31st
                    return ($currentDate->month === 6 || $currentDate->month === 12) && $currentDate->day === $currentDate->daysInMonth;
                }

            case 'yearly':
                // Check if it's the start or end of the year
                if ($timing === 'start_of_cycle') {
                    return $currentDate->month === 1 && $currentDate->day === 1;
                } else {
                    return $currentDate->month === 12 && $currentDate->day === $currentDate->daysInMonth;
                }

            default:
                return false;
        }
    }

    /**
     * Determine if a leave type should be processed based on its disbursement cycle within a financial year
     *
     * @param LeaveType $leaveType
     * @param Carbon $currentDate
     * @param FinancialYear $financialYear
     * @return bool
     */
    protected function shouldProcessDisbursementForFinancialYear(LeaveType $leaveType, Carbon $currentDate, FinancialYear $financialYear)
    {
        $cycle = $leaveType->disbursement_cycle;
        $timing = $leaveType->disbursement_time;

        // Calculate the financial year duration in months
        $financialYearStart = $financialYear->start_date;
        $financialYearEnd = $financialYear->end_date;
        $financialYearDuration = $financialYearStart->diffInMonths($financialYearEnd) + 1;

        // Calculate months since financial year start
        $monthsSinceStart = $financialYearStart->diffInMonths($currentDate);

        switch ($cycle) {
            case 'monthly':
                // For monthly cycles within financial year, check if it's the start or end of the month
                if ($timing === 'start_of_cycle') {
                    return $currentDate->day === 1;
                } else {
                    return $currentDate->day === $currentDate->daysInMonth;
                }

            case 'quarterly':
                // For quarterly cycles within financial year
                $quartersInYear = 4; // Standard 4 quarters
                $quarterLength = ceil($financialYearDuration / $quartersInYear);
                $currentQuarter = floor($monthsSinceStart / $quarterLength) + 1;

                if ($timing === 'start_of_cycle') {
                    // Start of quarter within financial year
                    $quarterStartMonth = $financialYearStart->copy()->addMonths(($currentQuarter - 1) * $quarterLength);
                    return $currentDate->month === $quarterStartMonth->month && $currentDate->day === 1;
                } else {
                    // End of quarter within financial year
                    $quarterEndMonth = $financialYearStart->copy()->addMonths($currentQuarter * $quarterLength - 1);
                    return $currentDate->month === $quarterEndMonth->month && $currentDate->day === $currentDate->daysInMonth;
                }

            case 'half_yearly':
                // For half-yearly cycles within financial year
                $halfYearLength = floor($financialYearDuration / 2);
                $currentHalf = floor($monthsSinceStart / $halfYearLength) + 1;

                if ($timing === 'start_of_cycle') {
                    // Start of half year within financial year
                    $halfStartMonth = $financialYearStart->copy()->addMonths(($currentHalf - 1) * $halfYearLength);
                    return $currentDate->month === $halfStartMonth->month && $currentDate->day === 1;
                } else {
                    // End of half year within financial year
                    $halfEndMonth = $financialYearStart->copy()->addMonths($currentHalf * $halfYearLength - 1);
                    return $currentDate->month === $halfEndMonth->month && $currentDate->day === $currentDate->daysInMonth;
                }

            case 'yearly':
                // For yearly cycles within financial year (start and end of financial year)
                if ($timing === 'start_of_cycle') {
                    return $currentDate->month === $financialYearStart->month && $currentDate->day === $financialYearStart->day;
                } else {
                    return $currentDate->month === $financialYearEnd->month && $currentDate->day === $financialYearEnd->day;
                }

            default:
                return false;
        }
    }
}
