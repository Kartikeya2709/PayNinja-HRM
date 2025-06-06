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
        $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : now();
        $dateString = $date->toDateString();
        // dd($dateString);
        
        $this->info("Marking leaves for date: " . $dateString);
        
        // Get all approved leave requests that cover the given date
        $leaveRequests = LeaveRequest::with(['employee', 'leaveType'])
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $dateString)
            ->whereDate('end_date', '>=', $dateString)
            ->get();
            
        $count = 0;
        $this->info("Found " . $leaveRequests->count() . " approved leave requests for date: " . $dateString);
        
        foreach ($leaveRequests as $leaveRequest) {
            $this->info("Processing leave request ID: " . $leaveRequest->id . " for employee ID: " . $leaveRequest->employee_id);
            try {
                $employee = $leaveRequest->employee;
                
                // Check if employee exists
                if (!$employee) {
                    $this->warn("Employee not found for leave request ID: " . $leaveRequest->id);
                    Log::warning('Employee not found for leave request', [
                        'leave_request_id' => $leaveRequest->id,
                        'employee_id' => $leaveRequest->employee_id
                    ]);
                    continue;
                }
                
                $this->info("Processing employee ID: " . $employee->id . " (" . $employee->name . ")");
                
                // Check if attendance is already marked for this date
                $existingAttendance = $employee->attendances()
                    ->whereDate('date', $dateString)
                    ->first();
                    
                $this->info("Existing attendance: " . ($existingAttendance ? 'Found' : 'Not found'));
                    
                if ($existingAttendance) {
                    // Update existing attendance to On Leave if it's not already marked as such
                    if ($existingAttendance->status !== 'On Leave') {
                        $existingAttendance->update([
                            'status' => 'On Leave',
                            'check_in_status' => 'On Leave',
                            'leave_request_id' => $leaveRequest->id,
                            'remarks' => 'On approved leave: ' . ($leaveRequest->leaveType->name ?? 'Leave'),
                        ]);
                        $count++;
                    }
                } else {
                    // Create new attendance record
                    $settings = $this->attendanceService->getAttendanceSettings($employee->company_id);
                    
                    if (!$settings) {
                        $this->error("Attendance settings not found for company ID: " . $employee->company_id);
                        Log::error('Attendance settings not found for company', ['company_id' => $employee->company_id]);
                        continue;
                    }
                    
                    $employee->attendances()->create([
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
                }
                
                $this->info("Successfully marked employee ID " . $employee->id . " as On Leave for " . $dateString);
                Log::info('Marked employee as On Leave', [
                    'employee_id' => $employee->id,
                    'date' => $dateString,
                    'leave_request_id' => $leaveRequest->id
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to mark employee as On Leave', [
                    'employee_id' => $leaveRequest->employee_id ?? null,
                    'date' => $dateString,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $this->error("Error processing leave for employee ID: " . ($leaveRequest->employee_id ?? 'unknown'));
                $this->error($e->getMessage());
            }
        }
        
        $this->info("Successfully marked {$count} employees as On Leave for {$dateString}");
        return 0;
    }
}
