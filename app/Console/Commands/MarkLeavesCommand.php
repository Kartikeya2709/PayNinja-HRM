<?php

namespace App\Console\Commands;

use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkLeavesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-leaves {date? : The date to mark leaves for (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark attendance as On Leave for employees with approved leave requests';

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
            
            $logMessage = "Marking leaves for date: " . $dateString;
            $this->info($logMessage);
            Log::info($logMessage);
            
            // Get all approved leave requests that cover the given date
            $leaveRequests = LeaveRequest::with(['employee', 'leaveType'])
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $dateString)
                ->whereDate('end_date', '>=', $dateString)
                ->get();
                
            $count = 0;
            $logMessage = "Found " . $leaveRequests->count() . " approved leave requests for date: " . $dateString;
            $this->info($logMessage);
            Log::info($logMessage);
        
            foreach ($leaveRequests as $leaveRequest) {
                $logContext = [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                    'date' => $dateString
                ];
                
                $logMessage = "Processing leave request ID: " . $leaveRequest->id . " for employee ID: " . $leaveRequest->employee_id;
                $this->info($logMessage);
                Log::info($logMessage, $logContext);
                
                // Get working days from the leave request
                $workingDays = is_array($leaveRequest->working_days) ? $leaveRequest->working_days : [];

                // Skip if no working days are specified
                if (empty($workingDays)) {
                    $logMessage = "No working days specified for leave request ID: " . $leaveRequest->id . ". Skipping...";
                    $this->warn($logMessage);
                    Log::warning($logMessage, $logContext);
                    continue;
                }
                
                // Skip if the current date is not in the working days
                if (!in_array($dateString, $workingDays)) {
                    $logMessage = "Skipping date " . $dateString . " as it's not a working day in the leave request";
                    $this->info($logMessage);
                    Log::info($logMessage, $logContext);
                    continue;
                }
            
                try {
                    $employee = $leaveRequest->employee;
                    
                    // Update log context with employee details
                    $logContext['employee_name'] = $employee ? $employee->name : 'Unknown';
                    
                    // Check if employee exists
                    if (!$employee) {
                        $logMessage = "Employee not found for leave request ID: " . $leaveRequest->id;
                        $this->warn($logMessage);
                        Log::warning($logMessage, $logContext);
                        continue;
                    }
                    
                    $logMessage = "Processing employee ID: " . $employee->id . " (" . $employee->name . ")";
                    $this->info($logMessage);
                    Log::info($logMessage, $logContext);
                    
                    // Check if attendance is already marked for this date
                    $existingAttendance = $employee->attendances()
                        ->whereDate('date', $dateString)
                        ->first();
                        
                    $logMessage = "Existing attendance: " . ($existingAttendance ? 'Found' : 'Not found');
                    $this->info($logMessage);
                    Log::info($logMessage, $logContext);
                        
                    if ($existingAttendance) {
                        // Only update existing attendance to On Leave if it's not already Holiday or Week-Off
                        if (!in_array($existingAttendance->status, ['Holiday', 'Week-Off', 'On Leave'])) {
                            $existingAttendance->update([
                                'status' => 'On Leave',
                                'check_in_status' => 'On Leave',
                                'leave_request_id' => $leaveRequest->id,
                                'remarks' => 'On approved leave: ' . ($leaveRequest->leaveType->name ?? 'Leave'),
                            ]);
                            $count++;
                            $logMessage = "Successfully updated employee ID " . $employee->id . " to On Leave for " . $dateString;
                            $this->info($logMessage);
                            Log::info($logMessage, $logContext);
                        } else {
                            $logMessage = "Skipping employee ID " . $employee->id . ". Already marked as " . $existingAttendance->status . ".";
                            $this->info($logMessage);
                            Log::info($logMessage, $logContext);
                        }
                    } else {
                        // Create new attendance record only if it's not a weekend or holiday
                        $settings = $this->attendanceService->getAttendanceSettings($employee->company_id);
                        
                        if (!$settings) {
                            $logMessage = "Attendance settings not found for company ID: " . $employee->company_id;
                            $this->error($logMessage);
                            Log::error($logMessage, array_merge($logContext, [
                                'company_id' => $employee->company_id
                            ]));
                            continue;
                        }

                        // Before creating, check if it's already marked as Holiday or Week-Off
                        // This might happen if MarkHolidayAttendance or MarkWeekendAsWeekoff ran earlier and created a record
                        $preExistingAttendance = $employee->attendances()
                            ->whereDate('date', $dateString)
                            ->first();

                        if ($preExistingAttendance && in_array($preExistingAttendance->status, ['Holiday', 'Week-Off'])) {
                            $logMessage = "Skipping creation for employee ID " . $employee->id . ". Pre-existing attendance is " . $preExistingAttendance->status . ".";
                            $this->info($logMessage);
                            Log::info($logMessage, $logContext);
                        } else {
                            $attendance = $employee->attendances()->create([
                                'date' => $dateString,
                                'status' => 'On Leave',
                                'check_in_status' => 'On Leave',
                                'leave_request_id' => $leaveRequest->id,
                                'remarks' => 'On approved leave: ' . ($leaveRequest->leaveType->name ?? 'Leave'),
                                'office_start_time' => $settings->office_start_time ?? '09:00:00',
                                'office_end_time' => $settings->office_end_time ?? '18:00:00',
                                'grace_period' => $settings->grace_period ?? '00:15:00',
                            ]);
                            $count++;
                            $logMessage = "Successfully marked employee ID " . $employee->id . " as On Leave for " . $dateString;
                            $this->info($logMessage);
                            Log::info($logMessage, array_merge($logContext, [
                                'attendance_id' => $attendance->id
                            ]));
                        }
                    }
                
                    // Removed redundant info log, already logged inside if/else blocks
                    Log::info('Marked employee as On Leave', [
                        'employee_id' => $employee->id,
                        'date' => $dateString,
                        'leave_request_id' => $leaveRequest->id
                    ]);
                
                } catch (\Exception $e) {
                    $errorMessage = "Error processing leave request ID " . $leaveRequest->id . ": " . $e->getMessage();
                    $this->error($errorMessage);
                    Log::error($errorMessage, array_merge($logContext, [
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]));
                }    
            }
            
            $logMessage = "Successfully marked " . $count . " employees as On Leave for " . $dateString;
            $this->info($logMessage);
            Log::info($logMessage, [
                'date' => $dateString,
                'leave_requests_processed' => $leaveRequests->count(),
                'employees_marked' => $count
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $errorMessage = 'Error in MarkLeavesCommand: ' . $e->getMessage();
            $this->error($errorMessage);
            Log::error($errorMessage, [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
