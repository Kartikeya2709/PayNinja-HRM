<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Company;
use App\Models\Leave;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\LeaveRequest;

class MarkWeekendAsWeekoff extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-weekend {date? : The date to mark as weekend (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark attendance as weekend for all employees based on company settings';

    /**
     * The attendance service instance.
     *
     * @var \App\Services\AttendanceService
     */
    protected $attendanceService;

    /**
     * Create a new command instance.
     *
     * @param  \App\Services\AttendanceService  $attendanceService
     * @return void
     */
    public function __construct(AttendanceService $attendanceService)
    {
        parent::__construct();
        $this->attendanceService = $attendanceService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : now();
            $dateString = $date->toDateString();
            
            $logMessage = "Marking weekend attendance for date: {$dateString}";
            $this->info($logMessage);
            Log::info($logMessage);
            
            // Log the current day for debugging
            $dayOfWeek = strtolower($date->format('l'));
            $logMessage = "Day of week: {$dayOfWeek}";
            $this->info($logMessage);
            Log::info($logMessage);
            
            // Get all companies that have employees
            $companyIds = \App\Models\Employee::select('company_id')
                ->distinct()
                ->pluck('company_id')
                ->filter()
                ->values()
                ->toArray();

            if (empty($companyIds)) {
                $logMessage = 'No companies with employees found.';
                $this->warn($logMessage);
                Log::info($logMessage);
                return 0;
            }
            
            $markedCount = 0;
            $logMessage = 'Found ' . count($companyIds) . ' companies with employees';
            $this->info($logMessage);
            Log::info($logMessage);
            
            foreach ($companyIds as $companyId) {
                try {
                    // Set the company ID for the attendance service
                    $this->attendanceService->setCompanyId($companyId);
                    
                    // Get the settings for logging purposes
                    $settings = $this->attendanceService->getAttendanceSettings($companyId);
                    $weekendDays = $settings ? ($settings->weekend_days ?? []) : [];
                    
                    $logMessage = "Processing company ID {$companyId} with weekend configuration: " . json_encode($weekendDays);
                    $this->info($logMessage);
                    Log::info($logMessage);
                    
                    // Check if the date is a weekend for this company
                    if ($this->attendanceService->isWeekend($date)) {
                        $logMessage = "Date {$dateString} is a weekend for company ID: {$companyId}";
                        $this->info($logMessage);
                        Log::info($logMessage);
                        
                        // Mark employees for this company
                        $marked = $this->markEmployeesForCompany($companyId, $date);
                        $markedCount += $marked;
                        
                        $logMessage = "Marked {$marked} employees as weekend-off for company ID: {$companyId}";
                        $this->info($logMessage);
                        Log::info($logMessage);
                    } else {
                        $logMessage = "Date {$dateString} is not a weekend for company ID: {$companyId}";
                        $this->info($logMessage);
                        Log::info($logMessage);
                        
                        // Log why it's not a weekend
                        $dayOfWeek = strtolower($date->format('l'));
                        $logMessage = "Day of week: {$dayOfWeek}, Weekend days: " . json_encode($weekendDays);
                        $this->info($logMessage);
                        Log::info($logMessage);
                        
                        // If it's a Saturday, log which Saturday of the month it is
                        if ($dayOfWeek === 'saturday') {
                            $saturdayOfMonth = (int)ceil($date->day / 7);
                            $logMessage = "This is the {$saturdayOfMonth} Saturday of the month";
                            $this->info($logMessage);
                            Log::info($logMessage);
                            
                            // Check for special Saturday patterns
                            if (in_array('saturday_1_3', $weekendDays)) {
                                $logMessage = "Company is configured for 1st and 3rd Saturdays off";
                                $this->info($logMessage);
                                Log::info($logMessage);
                                
                                if (in_array($saturdayOfMonth, [1, 3])) {
                                    $logMessage = "This should be a weekend (1st/3rd Saturday)";
                                    $this->info($logMessage);
                                    Log::info($logMessage);
                                }
                            }
                            
                            if (in_array('saturday_2_4', $weekendDays)) {
                                $logMessage = "Company is configured for 2nd and 4th Saturdays off";
                                $this->info($logMessage);
                                Log::info($logMessage);
                                
                                if (in_array($saturdayOfMonth, [2, 4])) {
                                    $logMessage = "This should be a weekend (2nd/4th Saturday)";
                                    $this->info($logMessage);
                                    Log::info($logMessage);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $errorMessage = "Error processing company {$companyId}: " . $e->getMessage();
                    $this->error($errorMessage);
                    Log::error($errorMessage, [
                        'company_id' => $companyId,
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]);
                    continue; // Continue with next company even if one fails
                }
            }
            
            $logMessage = "Successfully marked {$markedCount} employees as weekend-off.";
            $this->info($logMessage);
            Log::info($logMessage);
            
            return 0;
        } catch (\Exception $e) {
            $errorMessage = "Error in MarkWeekendAsWeekoff command: " . $e->getMessage();
            $this->error($errorMessage);
            Log::error($errorMessage, [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
    
    /**
     * Mark all active employees of a company as weekend for the given date
     * 
     * @param int $companyId
     * @param Carbon $date
     * @return int Number of employees marked as weekend
     */
    protected function markEmployeesForCompany($companyId, $date)
    {
        $dateString = $date->toDateString();
        $marked = 0;
        
        // Get all active employees for the company
        $employees = \App\Models\Employee::where('company_id', $companyId)
            // ->where('status', 'active')
            ->with('user') // Eager load the user relationship
            ->get();
            // dd($employees->user->name);
        if ($employees->isEmpty()) {
            $this->warn("No active employees found for company ID: {$companyId}");
            return 0;
        }
            
        $this->info("Found " . $employees->count() . " active employees for company ID: " . $companyId);
            
        foreach ($employees as $employee) {
            try {
                $this->info("Processing employee ID: {$employee->id}, Name: " . ($employee->user->name ?? 'N/A'));
                
                // Check if the employee has an active leave for this date
                $leave = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $dateString)
                    ->whereDate('end_date', '>=', $dateString)
                    ->first();

                if ($leave) {
                    // Check if this specific date is a working day during their leave
                    $workingDays = $leave->working_days ?? [];
                    $isWorkingDay = in_array($dateString, $workingDays);
                    
                    if ($isWorkingDay) {
                        $this->info("Skipping employee ID {$employee->id} - On approved leave with working day on {$dateString}");
                        continue;
                    } else {
                        $this->info("Employee ID {$employee->id} is on leave but {$dateString} is not a working day, marking as Week-Off");
                    }
                }
                
                // Check if attendance already exists for this date
                $attendance = \App\Models\Attendance::firstOrNew([
                    'employee_id' => $employee->id,
                    'date' => $dateString,
                ]);
                
                $this->info(sprintf(
                    'Employee ID %d - Current status: %s, Exists: %s',
                    $employee->id,
                    $attendance->status ?? 'none',
                    $attendance->exists ? 'yes' : 'no'
                ));
                
                // Only update if not already marked as Holiday or Week-Off
                if (!in_array($attendance->status, ['Holiday', 'Week-Off']) || !$attendance->exists) {
                    $this->info("Marking employee ID {$employee->id} as Week-Off");
                    
                    $attendance->fill([
                        'status' => 'Week-Off',
                        'check_in' => null,
                        'check_out' => null,
                        'total_working_hours' => 0,
                        'is_weekend' => true,
                        'notes' => 'Marked as week-off by system',
                    ]);
                    
                    $attendance->save();
                    $marked++;
                    $this->info("Successfully marked employee ID {$employee->id} as Week-Off");
                } else {
                    $this->info("Employee ID {$employee->id} is already marked as " . $attendance->status . ". Skipping Week-Off.");
                }
            } catch (\Exception $e) {
                Log::error("Error marking employee {$employee->id} as weekend: " . $e->getMessage(), [
                    'employee_id' => $employee->id,
                    'date' => $dateString,
                    'error' => $e->getMessage()
                ]);
                $this->error("Error processing employee ID {$employee->id}: " . $e->getMessage());
            }
        }
        
        return $marked;
    }
}
