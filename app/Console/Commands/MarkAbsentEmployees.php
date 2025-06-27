<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MarkAbsentEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absent {date? : The date to mark absences for (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark employees as absent if they have not checked in';

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
    // public function handle()
    // {
    //     $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : now()->subDay();
        
    //     $this->info("Marking absent employees for date: " . $date->toDateString());
        
    //     $count = $this->attendanceService->markAbsentEmployees($date);

    //     $this->info("Marked {$count} employees as absent.");
        
    //     return 0;
    // }

    public function handle()
    {
        try {
            $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : now()->subDay();
            $dateString = $date->toDateString();

            $logMessage = "Marking absent employees for date: " . $dateString;
            $this->info($logMessage);
            Log::info($logMessage);

            // Get all employees who haven't checked in for the date
            $employees = \App\Models\Employee::whereDoesntHave('attendances', function($query) use ($dateString) {
                $query->whereDate('date', $dateString);
            })->get();

            $markedAbsent = 0;
            $absentNames = [];
            $skippedCount = 0;
            $errorCount = 0;
            
            $logMessage = "Found " . $employees->count() . " employees without attendance records for " . $dateString;
            $this->info($logMessage);
            Log::info($logMessage);

            foreach ($employees as $employee) {
                $logContext = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->user->name ?? $employee->name,
                    'company_id' => $employee->company_id,
                    'date' => $dateString
                ];
                
                try {
                    // Check if an attendance record already exists for this employee and date
                    $existingAttendance = $employee->attendances()
                        ->whereDate('date', $dateString)
                        ->first();

                    // Only mark as absent if no existing attendance or if it's not already a higher priority status
                    if (!$existingAttendance || !in_array($existingAttendance->status, ['Holiday', 'Week-Off', 'On Leave', 'Present', 'Half Day', 'Late'])) {
                        // Get company-specific settings
                        $settings = $this->attendanceService->getAttendanceSettings($employee->company_id);
                        
                        if (!$settings) {
                            $errorMessage = "Attendance settings not found for company ID: " . $employee->company_id;
                            $this->error($errorMessage);
                            Log::error($errorMessage, $logContext);
                            $errorCount++;
                            continue;
                        }

                        if ($existingAttendance) {
                            // Update existing record to Absent if it's not a higher priority
                            $existingAttendance->update([
                                'status' => 'Absent',
                                'check_in_status' => 'Absent',
                                'remarks' => 'Automatically marked as absent by system',
                            ]);
                            
                            $logMessage = "Updated existing attendance to Absent for employee: " . ($employee->user->name ?? $employee->name);
                            $this->info($logMessage);
                            Log::info($logMessage, array_merge($logContext, [
                                'attendance_id' => $existingAttendance->id,
                                'action' => 'updated'
                            ]));
                        } else {
                            // Create absent record
                            $attendance = $employee->attendances()->create([
                                'date' => $dateString,
                                'status' => 'Absent',
                                'check_in_status' => 'Absent',
                                'office_start_time' => $settings->office_start_time ?? '09:00:00',
                                'office_end_time' => $settings->office_end_time ?? '18:00:00',
                                'grace_period' => $settings->grace_period ?? '00:15:00',
                                'remarks' => 'Automatically marked as absent by system',
                            ]);
                            
                            $logMessage = "Created new attendance as Absent for employee: " . ($employee->user->name ?? $employee->name);
                            $this->info($logMessage);
                            Log::info($logMessage, array_merge($logContext, [
                                'attendance_id' => $attendance->id,
                                'action' => 'created'
                            ]));
                        }

                        $markedAbsent++;
                        $absentNames[] = ($employee->user->name ?? $employee->name);
                        
                    } else {
                        $skipMessage = "Skipping employee: " . ($employee->user->name ?? $employee->name) . " as attendance is already marked as " . $existingAttendance->status;
                        $this->info($skipMessage);
                        Log::info($skipMessage, array_merge($logContext, [
                            'status' => $existingAttendance->status,
                            'action' => 'skipped'
                        ]));
                        $skippedCount++;
                    }
                
                } catch (\Exception $e) {
                    $errorMessage = "Error marking employee as absent (ID: {$employee->id}): " . $e->getMessage();
                    $this->error($errorMessage);
                    Log::error($errorMessage, array_merge($logContext, [
                        'exception' => $e,
                        'trace' => $e->getTraceAsString()
                    ]));
                    $errorCount++;
                }
            }

            // Log summary
            $summary = [
                'date' => $dateString,
                'total_employees_processed' => $employees->count(),
                'marked_absent' => $markedAbsent,
                'skipped' => $skippedCount,
                'errors' => $errorCount
            ];
            
            $logMessage = "Marked {$markedAbsent} employees as absent for {$dateString}";
            $this->info($logMessage);
            Log::info($logMessage, $summary);
            
            if ($markedAbsent > 0) {
                $this->info("Absent Employees (Total: {$markedAbsent}):");
                Log::info("Absent Employees List: " . implode(", ", $absentNames), [
                    'absent_employees' => $absentNames
                ]);
                
                foreach ($absentNames as $name) {
                    $this->line("- {$name}");
                }
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $errorMessage = 'Error in MarkAbsentEmployees command: ' . $e->getMessage();
            $this->error($errorMessage);
            Log::error($errorMessage, [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

}
