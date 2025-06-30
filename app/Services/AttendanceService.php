<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Shift;
use App\Models\AttendanceSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AttendanceService
{
    protected $companyId;

    /**
     * Set the company ID for the service
     *
     * @param int $companyId
     * @return void
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }
    /**
     * Record employee check-in
     */
    /**
     * Get office timings from settings
     */
    /**
     * Get attendance settings with geolocation info
     * 
     * @param int $companyId The company ID to get settings for
     * @return object|null
     */
    public function getAttendanceSettings($companyId = null)
    {
        // Use stored company ID if set and no company ID provided
        if (!$companyId && $this->companyId) {
            $companyId = $this->companyId;
        }
        // If still no company ID, try to get it from authenticated user
        if (!$companyId && Auth::check() && Auth::user()) {
            $companyId = Auth::user()->company_id;
        }

        // If still no company ID, return null
        if (!$companyId) {
            return null;
        }

        // Get settings for the specified company
        $settings = AttendanceSetting::where('company_id', $companyId)
            ->latest('updated_at')
            ->withoutGlobalScopes()
            ->first();

        if (!$settings) {
            return null;
        }
        
        // Format times to ensure they're in H:i:s format
        $formatTime = function($time) {
            if (empty($time)) return '00:00:00';
            $parts = explode(':', $time);
            if (count($parts) === 2) {
                return $time . ':00';
            }
            return $time;
        };
        
        return (object) [
            'office_start_time' => $formatTime($settings->office_start_time),
            'office_end_time' => $formatTime($settings->office_end_time),
            'grace_period' => $formatTime($settings->grace_period),
            'auto_absent_time' => $formatTime($settings->auto_absent_time),
            'work_hours' => (int) $settings->work_hours,
            'enable_geolocation' => (bool) $settings->enable_geolocation,
            'office_latitude' => $settings->office_latitude ? (float) $settings->office_latitude : null,
            'office_longitude' => $settings->office_longitude ? (float) $settings->office_longitude : null,
            'geofence_radius' => (int) $settings->geofence_radius,
            'weekend_days' => is_string($settings->weekend_days) 
                ? json_decode($settings->weekend_days, true) ?? [] 
                : (is_array($settings->weekend_days) ? $settings->weekend_days : []),
            'allow_multiple_check_in' => (bool) $settings->allow_multiple_check_in,
            'track_location' => (bool) $settings->track_location
        ];
    }
    
    /**
     * Check if location is within allowed radius
     * 
     * @param float $userLat User's latitude
     * @param float $userLng User's longitude
     * @param float $officeLat Office latitude
     * @param float $officeLng Office longitude
     * @param float $radiusMeters Allowed radius in meters
     * @return array [success, distance, message]
     */
    protected function isWithinAllowedRadius($userLat, $userLng, $officeLat, $officeLng, $radiusMeters)
    {
        if (!$userLat || !$userLng || !$officeLat || !$officeLng) {
            return [
                'success' => false,
                'distance' => null,
                'message' => 'Invalid coordinates provided.'
            ];
        }
        
        $earthRadius = 6371000; // Earth's radius in meters
        
        // Convert degrees to radians
        $lat1 = deg2rad($userLat);
        $lon1 = deg2rad($userLng);
        $lat2 = deg2rad($officeLat);
        $lon2 = deg2rad($officeLng);
        
        // Haversine formula
        $dlat = $lat2 - $lat1;
        $dlon = $lon2 - $lon1;
        
        $a = sin($dlat / 2) * sin($dlat / 2) +
             cos($lat1) * cos($lat2) *
             sin($dlon / 2) * sin($dlon / 2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        $isWithin = $distance <= $radiusMeters;
        
        return [
            'success' => $isWithin,
            'distance' => round($distance, 2),
            'message' => $isWithin 
                ? 'You are within the allowed area.'
                : 'You are ' . round($distance - $radiusMeters, 2) . ' meters outside the allowed area.'
        ];
    }
    
    /**
     * Get office timings (for backward compatibility)
     */
    protected function getOfficeTimings()
    {
        $settings = $this->getAttendanceSettings();
        return (object) [
            'office_start_time' => $settings->office_start_time,
            'office_end_time' => $settings->office_end_time,
            'grace_period' => $settings->grace_period,
            'work_hours' => $settings->work_hours
        ];
    }

    /**
     * Validate if a location is within the allowed area
     *
     * @param float $latitude
     * @param float $longitude
     * @return array
     */
    public function validateLocation($latitude, $longitude)
    {
        $settings = $this->getAttendanceSettings();
        
        // If geolocation is not required, return success
        if (!$settings->enable_geolocation) {
            return [
                'success' => true,
                'within_allowed_area' => true,
                'message' => 'Location validation is not required.'
            ];
        }
        
        // Check if office coordinates are set
        if (!$settings->office_latitude || !$settings->office_longitude) {
            return [
                'success' => false,
                'within_allowed_area' => false,
                'message' => 'Office location is not configured. Please contact your administrator.'
            ];
        }
        
        // Check if within allowed radius
        $result = $this->isWithinAllowedRadius(
            $latitude,
            $longitude,
            $settings->office_latitude,
            $settings->office_longitude,
            $settings->geofence_radius
        );
        
        // Try to get address if within allowed area
        $address = null;
        if ($result['success']) {
            try {
                $address = $this->getAddressFromCoordinates($latitude, $longitude);
            } catch (\Exception $e) {
                \Log::warning('Failed to get address from coordinates: ' . $e->getMessage());
            }
        }
        
        return [
            'success' => $result['success'],
            'within_allowed_area' => $result['success'],
            'distance' => $result['distance'],
            'message' => $result['message'],
            'address' => $address
        ];
    }
    
    /**
     * Get human-readable address from coordinates using reverse geocoding
     * 
     * @param float $latitude
     * @param float $longitude
     * @return string|null
     */
    protected function getAddressFromCoordinates($latitude, $longitude)
    {
        try {
            $apiKey = config('services.google.maps_api_key');
            $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$latitude},{$longitude}&key={$apiKey}";
            
            $response = file_get_contents($url);
            $data = json_decode($response, true);
            
            if ($data && $data['status'] === 'OK' && !empty($data['results'][0]['formatted_address'])) {
                return $data['results'][0]['formatted_address'];
            }
        } catch (\Exception $e) {
            \Log::error('Reverse geocoding failed: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Record employee check-in with optional geolocation
     *
     * @param Employee $employee
     * @param string|null $location
     * @param float|null $userLat
     * @param float|null $userLng
     * @param string|null $remarks
     * @return array
     */
    public function checkIn(Employee $employee, $location = null, $userLat = null, $userLng = null, $remarks = null)
    {
        // Use the application's timezone
        $timezone = config('app.timezone', 'UTC');
        $now = now($timezone);
        $today = $now->toDateString();
        $currentTime = $now->format('H:i:s');
        
        \Log::info('Starting check-in process', [
            'employee_id' => $employee->id,
            'time' => $now->toDateTimeString(),
            'location' => $location,
            'coordinates' => [$userLat, $userLng],
            'remarks' => $remarks
        ]);
        
        // Check if already checked in today
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();
            
        if ($existingAttendance) {
            return [
                'success' => false,
                'message' => 'You have already checked in today.',
                'error_type' => 'already_checked_in',
                'attendance' => $existingAttendance
            ];
        }

        // Get attendance settings
        $settings = $this->getAttendanceSettings();
        
        // Check geolocation if enabled
        if ($settings->enable_geolocation) {
            if ($userLat === null || $userLng === null) {
                return [
                    'success' => false,
                    'message' => 'Location is required for check-in'
                ];
            }
            
            $locationCheck = $this->isWithinAllowedRadius(
                $userLat, 
                $userLng, 
                $settings->office_latitude, 
                $settings->office_longitude, 
                $settings->geofence_radius
            );
            
            if (!$locationCheck['success']) {
                return [
                    'success' => false,
                    'message' => $locationCheck['message']
                ];
            }
            
            // Store the exact location with coordinates
            $location = $location ?: "$userLat,$userLng";
        }
        
        // Parse office timings with timezone
        $officeStart = Carbon::parse($today . ' ' . $settings->office_start_time, $timezone);
        $officeEnd = Carbon::parse($today . ' ' . $settings->office_end_time, $timezone);
        
        \Log::debug('Office timings', [
            'office_start' => $officeStart->toDateTimeString(),
            'office_end' => $officeEnd->toDateTimeString(),
            'grace_period' => $settings->grace_period
        ]);
        
        // Define earliest check-in time (30 minutes before office start)
        $earliestCheckIn = (clone $officeStart)->subMinutes(30);
        
        // Check if current time is before earliest allowed check-in
        if ($now->lt($earliestCheckIn)) {
            return [
                'success' => false,
                'message' => 'Check-in is only allowed starting from ' . $earliestCheckIn->format('h:i A'),
                'error_type' => 'too_early'
            ];
        }
        
        // Check if current time is after office end time
        if ($now->gt($officeEnd)) {
            return [
                'success' => false,
                'message' => 'Check-in is not allowed after office hours (' . $officeEnd->format('h:i A') . ')',
                'error_type' => 'after_hours'
            ];
        }
        
        // Parse grace period (format: i:s, e.g., 15:00 for 15 minutes)
        $graceMinutes = $this->parseGracePeriodToMinutes($settings->grace_period);
        $graceEnd = (clone $officeStart)->addMinutes($graceMinutes);
        
        // Debug log all timing information
        \Log::debug('Timing details', [
            'office_start' => $officeStart->toDateTimeString(),
            'grace_period_raw' => $settings->grace_period,
            'grace_minutes' => $graceMinutes,
            'grace_end' => $graceEnd->format('H:i:s'),
            'current_time' => $now->toDateTimeString(),
            'is_after_grace' => $now->gt($graceEnd) ? 'Yes' : 'No',
            'office_start_time' => $officeStart->format('H:i:s'),
            'grace_end_time' => $graceEnd->format('H:i:s'),
            'current_time_check' => $now->format('H:i:s'),
            'office_end_time' => $officeEnd->format('H:i:s')
        ]);


        
        // Check if it's a holiday or academic holiday
        $isHoliday = $this->isHoliday($today);
        $isAcademicHoliday = $this->isAcademicHoliday($today, $employee->company_id);
        
        if ($isAcademicHoliday) {
            $holidayName = $this->getHolidayName($today, $employee->company_id);
            
            return [
                'success' => false,
                'message' => "Check-ins are not allowed on Holiday : $holidayName",
                'error_type' => 'holiday_checkin'
            ];
        }
        
        // Check if it's a weekend
        $todayDay = strtolower($now->format('l'));
        $weekendDays = is_array($settings->weekend_days) 
            ? array_map('strtolower', $settings->weekend_days)
            : [];
            
        if (in_array($todayDay, $weekendDays)) {
            
            return [
                'success' => false,
                'message' => 'Check-ins are not allowed on weekends.',
                'error_type' => 'weekend_checkin'
            ];
        }
        
        // Initialize status and remarks
        $status = 'Present';
        $checkInStatus = 'On Time';
        $remarks = $remarks ?: '';
        
        // Grace period already calculated above
        
        // Check if check-in is before office start time (within 30 minutes)
        if ($now->lt($officeStart) && $now->gte($earliestCheckIn)) {
            $checkInStatus = 'Early';
            $status = 'Present';
            $earlyMinutes = $officeStart->diffInMinutes($now);
            $remarks = "Checked in {$earlyMinutes} minutes early. " . $remarks;
            
            \Log::info('Early check-in detected', [
                'minutes_early' => $earlyMinutes,
                'check_in_time' => $now->toTimeString(),
                'office_start' => $officeStart->toTimeString()
            ]);
        } 
        // Check if check-in is at or after office start but before grace period ends
        elseif ($now->gte($officeStart) && $now->lte($graceEnd)) {
            $checkInStatus = 'On Time';
            $status = 'Present';
            $remarks = 'Checked in on time. ' . $remarks;
            
            \Log::info('On-time check-in', [
                'check_in_time' => $now->toTimeString(),
                'office_start' => $officeStart->toTimeString(),
                'grace_end' => $graceEnd->toTimeString()
            ]);
        }
        // Check if check-in is after grace period
        elseif ($now->gt($graceEnd)) {
            $checkInStatus = 'Late';
            $status = 'Late';
            $lateMinutes = $now->diffInMinutes($graceEnd);
            $remarks = "Late by {$lateMinutes} minutes. " . $remarks;
            
            \Log::warning('Late check-in detected', [
                'minutes_late' => $lateMinutes,
                'check_in_time' => $now->toTimeString(),
                'grace_end' => $graceEnd->toTimeString()
            ]);
        }

        // Prepare check-in data with proper timezone handling
        $checkInData = [
            'employee_id' => $employee->id,
            'date' => $today,
            'check_in' => $currentTime,
            'check_in_location' => $location,
            'status' => $status,
            'check_in_status' => $checkInStatus,
            'check_in_remarks' => trim($remarks),
            'office_start_time' => $settings->office_start_time,
            'office_end_time' => $settings->office_end_time,
            'grace_period' => $settings->grace_period,
            'created_at' => $now,
            'updated_at' => $now
        ];
        
        // Add geolocation data if available
        if ($userLat && $userLng) {
            $checkInData['check_in_latitude'] = $userLat;
            $checkInData['check_in_longitude'] = $userLng;
        }
        
        try {
            $attendance = Attendance::create($checkInData);
            
            // Log the check-in
            $this->logAttendanceAction($employee, 'Check In', $location);
            
            \Log::info('Check-in successful', [
                'attendance_id' => $attendance->id,
                'status' => $status,
                'check_in_status' => $checkInStatus,
                'check_in_time' => $attendance->check_in
            ]);
            
            return [
                'success' => true,
                'message' => 'Checked in successfully!',
                'attendance' => $attendance,
                'check_in_status' => $checkInStatus
            ];
            
        } catch (\Exception $e) {
            \Log::error('Check-in failed', [
                'employee_id' => $employee->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'check_in_data' => $checkInData ?? null
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to save check-in. Please try again.',
                'error_type' => 'save_failed',
                'error_details' => config('app.debug') ? $e->getMessage() : null,
                'logged_at' => now()->toDateTimeString()
            ];
        }
    }
/**
 * Record employee check-out with optional geolocation
 *
 * @param Employee $employee
 * @param string|null $location
 * @param float|null $userLat
 * @param float|null $userLng
 * @param string|null $remarks
 * @return array
 */
public function checkOut(Employee $employee, $location = null, $userLat = null, $userLng = null, $remarks = null)
{
    $today = now()->format('Y-m-d');
    $now = now();
    
    try {
        // Check if already checked out today
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();
            
        if (!$attendance) {
            return [
                'success' => false,
                'message' => 'No check-in found for today. Please check in first.',
                'error_type' => 'no_check_in'
            ];
        }
        
        // Check if already checked out
        if ($attendance->check_out) {
            return [
                'success' => false,
                'message' => 'You have already checked out today.',
                'error_type' => 'already_checked_out',
                'attendance' => $attendance
            ];
        }

        // if ($attendance->check_out) {
        //     return [
        //         'success' => false,
        //         'message' => 'Already checked out today',
        //         'error_type' => 'already_checked_out'
        //     ];
        // }
        
        // Get attendance settings
        $settings = $this->getAttendanceSettings();
        
        // Check geolocation if enabled
        if ($settings->enable_geolocation) {
            if ($userLat === null || $userLng === null) {
                return [
                    'success' => false,
                    'message' => 'Location is required for check-out'
                ];
            }
            
            $locationCheck = $this->isWithinAllowedRadius(
                $userLat, 
                $userLng, 
                $settings->office_latitude, 
                $settings->office_longitude, 
                $settings->geofence_radius
            );
            
            if (!$locationCheck['success']) {
                return [
                    'success' => false,
                    'message' => $locationCheck['message']
                ];
            }
            
            // Store the exact location with coordinates
            $location = $location ?: "$userLat,$userLng";
        }
        
        // Prepare check-out data
        $checkOutData = [
            'check_out' => $now->format('H:i:s'),
            'check_out_location' => $location,
            'status' => $this->determineCheckOutStatus($attendance, $now)
        ];
        
        // Add remarks if provided
        if ($remarks) {
            $checkOutData['remarks'] = $attendance->remarks 
                ? $attendance->remarks . ' ' . $remarks 
                : $remarks;
        }
        
        // Add geolocation data if available
        if ($userLat && $userLng) {
            $checkOutData['check_out_latitude'] = $userLat;
            $checkOutData['check_out_longitude'] = $userLng;
        }
        
        $attendance->update($checkOutData);

        // Log the check-out
        $this->logAttendanceAction($employee, 'Check Out', $location);

        return [
            'success' => true,
            'message' => 'Checked out successfully!',
            'attendance' => $attendance
        ];
    } catch (\Exception $e) {
        \Log::error('Check-out failed: ' . $e->getMessage());
        // Get office timings
        $officeTimings = $this->getOfficeTimings();
        $checkIn = Carbon::parse($checkInTime);
        $officeStart = Carbon::parse($checkIn->toDateString() . ' ' . $officeTimings->office_start_time);
        $graceEnd = (clone $officeStart)->add($officeTimings->grace_period);

        if ($checkIn->gt($graceEnd)) {
            return 'Late';
        }

        return 'Present';
    }
}

    /**
     * Determine if check-out is early based on shift
     */
    protected function determineCheckOutStatus(Attendance $attendance, $checkOutTime)
    {
        // Get office timings and settings
        $officeTimings = $this->getOfficeTimings();
        $checkOut = Carbon::parse($checkOutTime);
        $checkIn = Carbon::parse($attendance->check_in);
        
        // Calculate expected check-out time based on check-in + work hours
        $expectedCheckOut = (clone $checkIn)->addHours($officeTimings->work_hours);
        
        // If checking out before completing work hours, mark as Half Day
        if ($checkOut->lt($expectedCheckOut)) {
            return 'Half Day';
        }
        
        // Original grace period logic remains for backward compatibility
        $officeEnd = Carbon::parse($checkOut->toDateString() . ' ' . $officeTimings->office_end_time);
        
        // Parse grace period (H:i:s) into a DateInterval
        $graceParts = explode(':', $officeTimings->grace_period);
        $graceInterval = new \DateInterval(sprintf(
            'PT%dH%dM%dS',
            $graceParts[0],
            $graceParts[1],
            $graceParts[2] ?? 0
        ));
        
        // Calculate grace end time
        $graceEnd = (clone $officeEnd)->add($graceInterval);
        
        // If checking out before grace period ends, it's on time
        if ($checkOut->lt($graceEnd)) {
            return $attendance->status; // Keep the original status (Present/Late)
        }
        
        // If checking out after grace period, check if it's a half day based on hours worked
        $hoursWorked = $checkOut->diffInHours($checkIn);
        if ($hoursWorked < ($officeTimings->work_hours / 2)) {
            return 'Half Day';
        }
        
        return $attendance->status;
    }

    /**
     * Log attendance action
     */
    protected function logAttendanceAction(Employee $employee, $action, $location = null)
    {
        $employee->attendanceLogs()->create([
            'action' => $action,
            'timestamp' => now(),
            'ip_address' => request()->ip(),
            'device_info' => request()->userAgent(),
            'location' => $location
        ]);
    }

    /**
     * Get monthly attendance summary for an employee
     * 
     * @param int $employeeId
     * @param string|null $month Format: Y-m
     * @return array
     */
    public function getMonthlySummary($employeeId, $month = null)
    {
        $month = $month ?: now()->format('Y-m');
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $workingDays = 0;
        $currentDate = $startDate->copy();
        
        // Calculate working days (excluding weekends and holidays)
        while ($currentDate->lte($endDate)) {
            if (!$this->isWeekend($currentDate) && !$this->isHoliday($currentDate)) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return [
            'present' => $attendances->where('status', 'Present')->count(),
            'absent' => $attendances->where('status', 'Absent')->count(),
            'late' => $attendances->where('status', 'Late')->count(),
            'on_leave' => $attendances->where('status', 'On Leave')->count(),
            'half_day' => $attendances->where('status', 'Half Day')->count(),
            'holiday' => $attendances->where('status', 'Holiday')->count(),
            'total_working_days' => $workingDays,
            'days_worked' => $attendances->whereIn('status', ['Present', 'Late', 'Half Day'])->count(),
        ];
    }

    /**
     * Mark absent employees for a given date
     * 
     * @param string|null $date Date to mark absences for (Y-m-d)
     * @return int Number of employees marked as absent
     */
    public function markAbsentEmployees($date = null)
    {
        $user=auth()->user();
        $now = now();
        $date = $date ? Carbon::parse($date) : $now;
        $dateString = $date->toDateString();
        // dd($user);
        // Get all unique company IDs that have employees
        $companyIds = Employee::distinct()->pluck('company_id');
        
        // If no companies found, return early
        if ($companyIds->isEmpty()) {
            \Log::info('No companies found with employees, skipping auto-absence marking');
            return 0;
        }
        
        $markedAbsent = 0;
        $absentNames = [];
        
        // Process each company separately
        foreach ($companyIds as $companyId) {
            // Get settings for this company
            $settings = $this->getAttendanceSettings($companyId);
            
            // Skip if no settings found for company or auto_absent_time not set
            if (!$settings || empty($settings->auto_absent_time)) {
                \Log::info('Skipping auto-absence - no settings for company', [
                    'company_id' => $companyId
                ]);
                continue;
            }
            
            // Parse the auto absent time for this company
            $autoAbsentDateTime = Carbon::parse($dateString . ' ' . $settings->auto_absent_time);
            
            // If current time is before auto absent time, don't mark anyone as absent yet
            if ($now->lt($autoAbsentDateTime)) {
                \Log::info('Current time is before auto absent time, skipping auto-absence marking for company', [
                    'company_id' => $companyId,
                    'current_time' => $now->toDateTimeString(),
                    'auto_absent_time' => $autoAbsentDateTime->toDateTimeString()
                ]);
                continue;
            }
            
            // Don't mark absences on weekends or holidays for this company
            if ($this->isWeekend($date)) {
                continue;
            }
            
            if ($this->isHoliday($date)) {
                // Create or update attendance record with status 'Holiday'
            //     Attendance::updateOrCreate(
            //         [
            //             'employee_id' => $employee->id,
            //             'date' => $dateString
            //         ],
            //         [
            //             'status' => 'Holiday',
            //             'check_in_status' => 'Holiday',
            //             'check_in_remarks' => 'Holiday - ' . $this->getHolidayName($date, $companyId),
            //             'office_start_time' => $settings->office_start_time,
            //             'office_end_time' => $settings->office_end_time,
            //             'grace_period' => $settings->grace_period
            //         ]
            //     );
                continue;
            }
            
            // Get employees who didn't check in for this company
            $employees = Employee::where('company_id', $companyId)
                ->whereDoesntHave('attendances', function($query) use ($dateString) {
                    $query->where('date', $dateString);
                })
                ->get();
                
            $companyMarkedAbsent = 0;
                
            foreach ($employees as $employee) {
                // Check if employee is on leave
                if ($this->isOnLeave($employee, $date)) {
                    // Create leave record instead of absent
                    try {
                        $leaveRequest = $employee->leaveRequests()
                            ->where('status', 'approved')
                            ->whereDate('start_date', '<=', $dateString)
                            ->whereDate('end_date', '>=', $dateString)
                            ->first();
                            
                        if ($leaveRequest) {
                            Attendance::create([
                                'employee_id' => $employee->id,
                                'date' => $dateString,
                                'status' => 'On Leave',
                                'check_in_status' => 'On Leave',
                                'leave_request_id' => $leaveRequest->id,
                                'remarks' => 'On approved leave: ' . ($leaveRequest->leaveType->name ?? 'Leave'),
                                'office_start_time' => $settings->office_start_time,
                                'office_end_time' => $settings->office_end_time,
                                'grace_period' => $settings->grace_period,
                                'created_at' => $now,
                                'updated_at' => $now
                            ]);
                            
                            \Log::info('Marked employee as on leave', [
                                'employee_id' => $employee->id,
                                'date' => $dateString,
                                'leave_request_id' => $leaveRequest->id
                            ]);
                            continue;
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to mark employee as on leave', [
                            'employee_id' => $employee->id,
                            'date' => $dateString,
                            'error' => $e->getMessage()
                        ]);
                    }
                }


                // If we reach here, the employee is not on leave and hasn't checked in
                try {
                    // Mark as absent
                    Attendance::create([
                        'employee_id' => $employee->id,
                        'date' => $dateString,
                        'status' => 'Absent',
                        'check_in_status' => 'Absent',
                        'remarks' => 'Marked absent by system - No check-in recorded',
                        'office_start_time' => $settings->office_start_time,
                        'office_end_time' => $settings->office_end_time,
                        'grace_period' => $settings->grace_period,
                        'created_at' => $now,
                        'updated_at' => $now
                    ]);

                    $markedAbsent++;
                    $companyMarkedAbsent++;
                    $absentNames[] = $employee->name; // ← Add this line to collect the name

                    
                    \Log::info('Marked employee as absent', [
                        'employee_id' => $employee->id,
                        'company_id' => $companyId,
                        'date' => $dateString,
                        'auto_absent_time' => $settings->auto_absent_time
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to mark employee as absent', [
                        'employee_id' => $employee->id,
                        'company_id' => $companyId,
                        'date' => $dateString,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } // End of employee loop
            
            // Log summary for this company
            \Log::info('Finished processing auto-absence for company', [
                'company_id' => $companyId,
                'date' => $dateString,
                'employees_processed' => $employees->count(),
                'marked_absent' => $companyMarkedAbsent
            ]);
        } // End of company loop
        
        // return $markedAbsent;
        return [
            'count' => $markedAbsent,
            'names' => $absentNames,
        ];
        
    }

    /**
     * Check if a date is a weekend based on company settings.
     *
     * @param \Carbon\Carbon|string $date
     * @return bool
     */
    public function isWeekend($date): bool
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        $settings = $this->getAttendanceSettings();

        // Default to Sunday being a weekend if no settings are found or weekend_days is empty
        if (!$settings || empty($settings->weekend_days)) {
            return $date->isSunday();
        }
        \Log::debug('check the days :', ['weekend_days' => $settings->weekend_days]);
        $weekendDays = $settings->weekend_days; // This is an array from the model cast
        $dayName = $date->format('l'); // Full day name, e.g., "Sunday"

        // Check for regular full-day weekends (e.g., Sunday, or if "Saturday" is selected for all Saturdays)
        if (in_array($dayName, $weekendDays)) {
            return true;
        }

        // Handle alternate Saturdays if the day is a Saturday
        if ($date->isSaturday()) {
            // This calculates which occurrence of the day it is in the month (e.g., 1st, 2nd, 3rd Saturday)
            $saturdayOfMonth = (int)ceil($date->day / 7);

            // 1st and 3rd Saturday off
            if (in_array('saturday_1_3', $weekendDays) && in_array($saturdayOfMonth, [1, 3])) {
                return true;
            }
            
            // 2nd and 4th Saturday off
            if (in_array('saturday_2_4', $weekendDays) && in_array($saturdayOfMonth, [2, 4])) {
                return true;
            }

            // 1st, 3rd, and 5th Saturday off
            if (in_array('saturday_1_3_5', $weekendDays) && in_array($saturdayOfMonth, [1, 3, 5])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a date is a holiday
     */
    /**
     * Parse grace period string (H:i:s) to minutes
     * 
     * @param string $gracePeriod
     * @return int
     */
    protected function parseGracePeriodToMinutes($gracePeriod)
    {
        // If it's already a number, assume it's in minutes
        if (is_numeric($gracePeriod)) {
            return (int)$gracePeriod;
        }
        
        // Handle i:s format (e.g., 15:00 for 15 minutes)
        $parts = array_map('intval', explode(':', $gracePeriod));
        
        // If format is i:s (2 parts)
        if (count($parts) === 2) {
            return $parts[0]; // Return just the minutes part
        }
        
        // If it's a single number, assume it's minutes
        return (int)$gracePeriod;
    }

    /**
     * Check if a date is a holiday
     *
     * @param string|\Carbon\Carbon $date
     * @return bool
     */
    public function isHoliday($date)
    {
        // Convert to Carbon instance if it's a string
        $date = is_string($date) ? Carbon::parse($date) : $date;
        $dateString = $date->toDateString();

        // Get company ID from authenticated user if available
        $companyId = null;
        if (Auth::check()) {
            $companyId = Auth::user()->company_id;
        }


        // Check AcademicHoliday (date range based)
        $isAcademicHoliday = \App\Models\AcademicHoliday::where('company_id', $companyId)
            ->whereDate('from_date', '<=', $dateString)
            ->whereDate('to_date', '>=', $dateString)
            ->exists();

        return $isAcademicHoliday;
    }

    /**
     * Check if employee is on leave for a specific date
     *
     * @param \App\Models\Employee $employee
     * @param string|\Carbon\Carbon $date
     * @return bool
     */
    protected function isOnLeave($employee, $date)
    {
        $date = is_string($date) ? Carbon::parse($date) : $date;
        $dateString = $date->toDateString();
        
        // Check if there's an approved leave request that covers this date
        return $employee->leaveRequests()
            ->where('status', 'approved')
            ->where(function($query) use ($dateString) {
                $query->whereDate('start_date', '<=', $dateString)
                      ->whereDate('end_date', '>=', $dateString);
            })
            ->exists();
         
    }

    /**
     * Get holiday name for a specific date and company
     * 
     * @param string|\Carbon\Carbon $date
     * @param int $companyId
     * @return string
     */
    protected function getHolidayName($date, $companyId)
    {
        $date = is_string($date) ? Carbon::parse($date) : $date;
        $dateString = $date->toDateString();
        
        $holiday = \App\Models\Holiday::where('company_id', $companyId)
            ->whereDate('date', $dateString)
            ->first();
        
        if ($holiday) {
            return $holiday->name;
        }
        
        $academicHoliday = \App\Models\AcademicHoliday::where('company_id', $companyId)
            ->whereDate('from_date', '<=', $dateString)
            ->whereDate('to_date', '>=', $dateString)
            ->first();
        
        return $academicHoliday ? $academicHoliday->name : 'Holiday';
         
    }
    
    /**
     * Check if a date is an academic holiday
     *
     * @param string|Carbon $date
     * @param int $companyId
     * @return bool
     */
    protected function isAcademicHoliday($date, $companyId)
    {
        $date = is_string($date) ? Carbon::parse($date) : $date;
        $dateString = $date->toDateString();
        
        return \App\Models\AcademicHoliday::where('company_id', $companyId)
            ->whereDate('from_date', '<=', $dateString)
            ->whereDate('to_date', '>=', $dateString)
            ->exists();
    }     
}