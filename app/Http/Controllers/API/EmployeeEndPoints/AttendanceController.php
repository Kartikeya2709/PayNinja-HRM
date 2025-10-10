<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\API\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use Carbon\Carbon;

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

            $attendances = $query->latest()->get();

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