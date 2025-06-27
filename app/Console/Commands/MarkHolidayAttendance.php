<?php

namespace App\Console\Commands;

use App\Models\AcademicHoliday;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MarkHolidayAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-holidays {--date= : The date to check (Y-m-d), defaults to today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark attendance as Holiday for all employees on academic holidays';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
            $dateString = $date->toDateString();
            
            $logMessage = "Checking for holidays on: " . $dateString;
            $this->info($logMessage);
            Log::info($logMessage);
            
            // Get all companies that have academic holidays on this date
            $holidays = AcademicHoliday::whereDate('from_date', '<=', $dateString)
                ->whereDate('to_date', '>=', $dateString)
                ->with('company')
                ->get();
                
            if ($holidays->isEmpty()) {
                $logMessage = "No academic holidays found for " . $dateString;
                $this->info($logMessage);
                Log::info($logMessage);
                return 0;
            }
        
        $this->info("Found " . $holidays->count() . " academic holiday(s) on this date");
        
        $totalMarked = 0;
        
        foreach ($holidays as $holiday) {
            $company = $holiday->company;
            $logMessage = "Processing holiday '{$holiday->name}' for company: " . $company->name;
            $this->info($logMessage);
            Log::info($logMessage);
            
            // Get all active employees for this company
            $employees = Employee::where('company_id', $company->id)
                ->where('status', 'active')
                ->get();
                
            $logMessage = "Marking " . $employees->count() . " employees as Holiday";
            $this->info($logMessage);
            Log::info($logMessage);
            
            $markedCount = 0;
            $updatedCount = 0;
            
            foreach ($employees as $employee) {
                // Check if attendance already exists for this date and employee
                $existingAttendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', $dateString)
                    ->first();
                    
                if (!$existingAttendance) {
                    // Create new attendance record
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateString,
                        'status' => 'Holiday',
                        'remarks' => 'Academic Holiday: ' . $holiday->name,
                        'created_by' => 1, // System user
                        'updated_by' => 1, // System user
                    ]);
                    $markedCount++;
                } else if ($existingAttendance->status !== 'Holiday') {
                    // Update existing attendance if it's not already marked as Holiday
                    $existingAttendance->update([
                        'status' => 'Holiday',
                        'remarks' => 'Updated to Holiday - Academic Holiday: ' . $holiday->name,
                        'updated_by' => 1, // System user
                    ]);
                    $updatedCount++;
                }
            }
            
            $logMessage = "Holiday attendance completed for {$company->name}: {$markedCount} marked, {$updatedCount} updated";
            $this->info($logMessage);
            Log::info($logMessage);
        }
        
        $logMessage = "Holiday attendance marking completed for " . $dateString;
        $this->info($logMessage);
        Log::info($logMessage);
        
        return 0;
            
        } catch (\Exception $e) {
            $errorMessage = 'Error in MarkHolidayAttendance: ' . $e->getMessage();
            $this->error($errorMessage);
            Log::error($errorMessage, [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
