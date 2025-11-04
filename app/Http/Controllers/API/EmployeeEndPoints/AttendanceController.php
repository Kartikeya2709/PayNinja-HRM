<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\API\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use App\Services\AttendanceService;

class AttendanceController extends BaseApiController
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Get attendance history
     */
    public function getAttendanceHistory(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
                'month' => 'nullable|integer|between:1,12',
                'year' => 'nullable|integer|min:2000',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
            }

            $query = Attendance::where('employee_id', Auth::user()->employee->id);
            ;

            // Filter by date range or month
            if ($request->from_date && $request->to_date) {
                $query->whereBetween('date', [$request->from_date, $request->to_date]);
            } elseif ($request->month && $request->year) {
                $query->whereYear('date', $request->year)
                    ->whereMonth('date', $request->month);
            } else {
                // Default to current month
                $query->whereYear('date', now()->year)
                    ->whereMonth('date', now()->month);
            }

            $attendances = $query->orderBy('date', 'asc')->get();

            return $this->sendResponse([
                'attendances' => $attendances,
                'summary' => [
                    'present' => $attendances->where('status', 'Present')->count(),
                    'absent' => $attendances->where('status', 'Absent')->count(),
                    'late' => $attendances->where('status', 'Late')->count(),
                    'half_day' => $attendances->where('status', 'Half Day')->count(),
                    'leave' => $attendances->where('status', 'On Leave')->count(),
                ]
            ], 'Attendance history retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving attendance history', [$e->getMessage()], 500);
        }
    }

    /**
     * Check in
     */
    public function checkIn(Request $request)
    {
        \Log::info('Check-in method executed');
        try {
            $validator = validator($request->all(), [
                'check_in_location' => 'required|string',
                'device_info' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
            }

            $employee = Auth::user()->employee;
            $today = Carbon::today();

            // Check if already checked in
            $existingAttendance = $employee->attendances()
                ->whereDate('date', $today)
                ->first();

            if ($existingAttendance && $existingAttendance->check_in) {
                return $this->sendError('Already checked in', [], 400);
            }

            $validated = $validator->validated();

            // Parse coordinates from the location string
            $coordinates = explode(',', $validated['check_in_location']);
            $latitude = count($coordinates) > 0 ? trim($coordinates[0]) : null;
            $longitude = count($coordinates) > 1 ? trim($coordinates[1]) : null;

            // Use AttendanceService to handle check-in with proper status
            $result = $this->attendanceService->checkIn(
                $employee,
                $validated['check_in_location'],
                $latitude,
                $longitude,
            );

            if (!$result['success']) {
                return $this->sendError($result['message'], [], 400);
            }

            $attendance = $result['attendance'];

            // dd($attendance);

            // Log the check-in
            // $attendance->logs()->create([
            //     'type' => 'check_in',
            //     'time' => now(),
            //     'latitude' => $request->latitude,
            //     'longitude' => $request->longitude,
            //     'device_info' => $request->device_info,
            // ]);

            return $this->sendResponse(['attendance' => $attendance], 'Checked in successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error during check-in', [$e->getMessage()], 500);
        }
    }

    /**
     * Get attendance settings and today's attendance for mobile
     */
    public function getAttendanceSettingsAndToday(Request $request)
    {
        try {
            $employee = Auth::user()->employee;
            $today = Carbon::today();

            // Get attendance settings
            $settings = $this->attendanceService->getAttendanceSettings();

            if (!$settings) {
                return $this->sendError('Attendance settings not configured', [], 404);
            }

            // Get today's attendance
            $todayAttendance = $employee->attendances()
                ->whereDate('date', $today)
                ->first();

            // Check if today is weekend
            $isWeekend = $this->attendanceService->isWeekend($today);

            // Check if employee is exempt from geolocation
            $isExemptFromGeolocation = false;
            try {
                $settingsModel = \App\Models\AttendanceSetting::where('company_id', $employee->company_id)
                    ->latest('updated_at')
                    ->withoutGlobalScopes()
                    ->first();
                $isExemptFromGeolocation = $settingsModel ? $settingsModel->isEmployeeExemptFromGeolocation($employee->id) : false;
            } catch (\Throwable $t) {
                \Log::warning('Failed checking geolocation exemption: ' . $t->getMessage());
            }

            $response = [
                'todayAttendance' => $todayAttendance ? [
                    'id' => $todayAttendance->id,
                    'date' => $todayAttendance->date,
                    'check_in' => $todayAttendance->check_in,
                    'check_out' => $todayAttendance->check_out,
                    'status' => $todayAttendance->status,
                    'check_in_status' => $todayAttendance->check_in_status,
                    'check_in_location' => $todayAttendance->check_in_location,
                    'check_out_location' => $todayAttendance->check_out_location,
                    'check_in_latitude' => $todayAttendance->check_in_latitude,
                    'check_in_longitude' => $todayAttendance->check_in_longitude,
                    'check_out_latitude' => $todayAttendance->check_out_latitude,
                    'check_out_longitude' => $todayAttendance->check_out_longitude,
                    'remarks' => $todayAttendance->remarks,
                    'check_in_remarks' => $todayAttendance->check_in_remarks
                ] : null,
                'settings' => [
                    'class' => get_class($settings),
                    'properties' => [
                        'office_start_time' => $settings->office_start_time,
                        'office_end_time' => $settings->office_end_time,
                        'grace_period' => $settings->grace_period,
                        'auto_absent_time' => $settings->auto_absent_time,
                        'work_hours' => $settings->work_hours,
                        'enable_geolocation' => $settings->enable_geolocation,
                        'office_latitude' => $settings->office_latitude,
                        'office_longitude' => $settings->office_longitude,
                        'geofence_radius' => $settings->geofence_radius,
                        'weekend_days' => $settings->weekend_days,
                        'allow_multiple_check_in' => $settings->allow_multiple_check_in,
                        'track_location' => $settings->track_location
                    ]
                ],
                'isWeekend' => $isWeekend,
                'today' => $today->toDateString(),
                'isExemptFromGeolocation' => $isExemptFromGeolocation
            ];

            return $this->sendResponse($response, 'Attendance settings and today\'s attendance retrieved successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error retrieving attendance data', [$e->getMessage()], 500);
        }
    }

    /**
     * Check out
     */
    public function checkOut(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'check_out_location' => 'required|string',
                'device_info' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
            }

            $employee = Auth::user()->employee;
            $today = Carbon::today();

            // Get today's attendance
            $attendance = $employee->attendances()
                ->whereDate('date', $today)
                ->first();

            if (!$attendance || !$attendance->check_in) {
                return $this->sendError('No check-in found for today', [], 400);
            }

            if ($attendance->check_out) {
                return $this->sendError('Already checked out', [], 400);
            }

            // Parse coordinates from the location string
            $coordinates = explode(',', $request['check_out_location']);
            $latitude = count($coordinates) > 0 ? trim($coordinates[0]) : null;
            $longitude = count($coordinates) > 1 ? trim($coordinates[1]) : null;

            // Use AttendanceService to handle check-out with proper status
            $result = $this->attendanceService->checkOut(
                $employee,
                $request['check_out_location'], // keep original string for location
                $latitude,
                $longitude,
                null  // remarks
            );

            if (!$result['success']) {
                return $this->sendError($result['message'], [], 400);
            }

            $attendance = $result['attendance'];

            // Log the check-out
            // $attendance->logs()->create([
            //     'type' => 'check_out',
            //     'time' => now(),
            //     'latitude' => $request->latitude,
            //     'longitude' => $request->longitude,
            //     'device_info' => $request->device_info,
            // ]);

            return $this->sendResponse(['attendance' => $attendance], 'Checked out successfully');
        } catch (\Exception $e) {
            return $this->sendError('Error during check-out', [$e->getMessage()], 500);
        }
    }
}