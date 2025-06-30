<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected $attendanceService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\AttendanceService  $attendanceService
     * @return void
     */
    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Display the attendance dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return redirect()->route('home')->with('error', 'Employee record not found.');
        }

        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();

        $todayAttendance = $employee->attendances()
            ->whereDate('date', $today)
            ->first();

        $weekAttendances = $employee->attendances()
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->orderBy('date', 'desc')
            ->get();

        $monthlySummary = $this->attendanceService->getMonthlySummary($employee->id);

        return view('attendance.dashboard', compact(
            'todayAttendance',
            'weekAttendances',
            'monthlySummary'
        ));
    }

    /**
     * Display the check-in/out page.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkInOut()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('home')->with('error', 'Employee record not found.');
        }

        $today = now()->toDateString(); // Using Carbon instance for date logic
        // Check if today is a weekend using the service
        $isWeekend = $this->attendanceService->isWeekend($today);

        $todayAttendance = $employee->attendances()
            ->whereDate('date', $today)
            ->first();
        // Get attendance settings
        $settings = $this->attendanceService->getAttendanceSettings();
        return view('attendance.check-in-out', [
            'todayAttendance' => $todayAttendance,
            'settings' => $settings,
            'isWeekend' => $isWeekend,
            'today' => $today,
        ]);
    }
    
    /**
     * Get geolocation settings for the attendance system
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGeolocationSettings()
    {
        try {
            $settings = $this->attendanceService->getAttendanceSettings();
            
            if (!$settings) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance settings not found.'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'enable_geolocation' => (bool) $settings->enable_geolocation,
                    'office_latitude' => $settings->office_latitude ? (float) $settings->office_latitude : null,
                    'office_longitude' => $settings->office_longitude ? (float) $settings->office_longitude : null,
                    'geofence_radius' => (int) $settings->geofence_radius,
                    'track_location' => (bool) $settings->track_location
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to get geolocation settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve geolocation settings.'
            ], 500);
        }
    }

    /**
     * Process check-in.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * Process check-in with geolocation validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Check if the provided location is within the allowed geofence.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $result = $this->attendanceService->validateLocation(
            $request->latitude,
            $request->longitude
        );

        return response()->json($result);
    }

    /**
     * Process check-in with geolocation validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkIn(Request $request)
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found.'
                ], 404);
            }
            
            // Get geolocation data
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $location = $request->input('location');
            $remarks = $request->input('remarks');
            
            // Get attendance settings
            $settings = $this->attendanceService->getAttendanceSettings();
            
            // If location is provided as a string ("lat,lng"), parse it
            if (!$latitude && !$longitude && $location && strpos($location, ',') !== false) {
                list($latitude, $longitude) = explode(',', $location, 2);
                $latitude = trim($latitude);
                $longitude = trim($longitude);
            }
            
            // If geolocation is required but not provided
            if ($settings->enable_geolocation && (!$latitude || !$longitude)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location is required for check-in. Please enable location services.'
                ], 400);
            }
            
            // If geolocation is provided, validate it
            if ($settings->enable_geolocation && $latitude && $longitude) {
                $locationCheck = $this->attendanceService->validateLocation(
                    (float)$latitude,
                    (float)$longitude
                );
                
                if (!$locationCheck['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $locationCheck['message'],
                        'error_type' => 'location_validation_failed'
                    ], 400);
                }
                
                // Use reverse geocoded address if no location provided
                if (empty($location) && !empty($locationCheck['address'])) {
                    $location = $locationCheck['address'];
                }
            }
            
            // Call the service to handle check-in
            $result = $this->attendanceService->checkIn(
                $employee, 
                $location, 
                $latitude, 
                $longitude,
                $remarks
            );
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Checked in successfully!',
                    'attendance' => $result['attendance']
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'error_type' => $result['error_type'] ?? 'check_in_failed'
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('Check-in error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your check-in. Please try again.',
                'error_type' => 'server_error'
            ], 500);
        }
    }

    /**
     * Process check-out with geolocation validation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOut(Request $request)
    {
        try {
            $user = Auth::user();
            $employee = $user->employee;
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found.'
                ], 404);
            }
            
            // Get geolocation data
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $location = $request->input('location');
            $remarks = $request->input('remarks');
            
            // Get attendance settings
            $settings = $this->attendanceService->getAttendanceSettings();
            
            // If location is provided as a string ("lat,lng"), parse it
            if (!$latitude && !$longitude && $location && strpos($location, ',') !== false) {
                list($latitude, $longitude) = explode(',', $location, 2);
                $latitude = trim($latitude);
                $longitude = trim($longitude);
            }
            
            // If geolocation is required but not provided
            if ($settings->enable_geolocation && (!$latitude || !$longitude)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location is required for check-out. Please enable location services.'
                ], 400);
            }
            
            // If geolocation is provided, validate it
            if ($settings->enable_geolocation && $latitude && $longitude) {
                $locationCheck = $this->attendanceService->validateLocation(
                    (float)$latitude,
                    (float)$longitude
                );
                
                if (!$locationCheck['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $locationCheck['message'],
                        'error_type' => 'location_validation_failed'
                    ], 400);
                }
                
                // Use reverse geocoded address if no location provided
                if (empty($location) && !empty($locationCheck['address'])) {
                    $location = $locationCheck['address'];
                }
            }
            
            // Call the service to handle check-out
            $result = $this->attendanceService->checkOut(
                $employee, 
                $location, 
                $latitude, 
                $longitude,
                $remarks
            );
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Checked out successfully!',
                    'attendance' => $result['attendance']
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'error_type' => $result['error_type'] ?? 'check_out_failed'
            ], 400);
            
        } catch (\Exception $e) {
            \Log::error('Check-out error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your check-out. Please try again.',
                'error_type' => 'server_error'
            ], 500);
        }
    }

    /**
     * Handle attendance response.
     *
     * @param  array  $result
     * @return \Illuminate\Http\Response
     */
    private function handleAttendanceResponse($result)
    {
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'attendance' => $result['attendance']
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Display employee's attendance history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function myAttendance(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return redirect()->route('home')->with('error', 'Employee record not found.');
        }

        $month = $request->input('month', now()->format('Y-m'));
        
        $attendances = $employee->attendances()
            ->whereYear('date', '=', date('Y', strtotime($month)))
            ->whereMonth('date', '=', date('m', strtotime($month)))
            ->orderBy('date', 'desc')
            ->paginate(15);
            
        // Get monthly summary for the chart
        $monthlySummary = $this->attendanceService->getMonthlySummary($employee->id, $month);
        
        // Get all dates in the month for the chart
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();
        $dates = collect();
        
        while ($startDate->lte($endDate)) {
            $dates->push($startDate->copy());
            $startDate->addDay();
        }

        // Debug: Log the data being passed to the view
        \Log::info('Attendance Data for Chart', [
            'month' => $month,
            'total_attendances' => $attendances->count(),
            'monthly_summary' => $monthlySummary,
            'total_dates' => $dates->count(),
            'sample_date' => $dates->first()?->format('Y-m-d') . ' to ' . $dates->last()?->format('Y-m-d')
        ]);

        return view('attendance.my-attendance', [
            'attendances' => $attendances,
            'month' => $month,
            'monthlySummary' => $monthlySummary,
            'dates' => $dates
        ]);
    }

    /**
     * Get employee's attendance summary.
     *
     * @return \Illuminate\Http\Response
     */
    public function myAttendanceSummary()
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee record not found.'
            ], 404);
        }

        $currentMonth = now()->format('Y-m');
        
        $summary = $this->attendanceService->getMonthlySummary($employee->id, $currentMonth);

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }

    /**
     * Export employee's attendance history to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportAttendance(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return redirect()->route('home')->with('error', 'Employee record not found.');
        }

        $month = $request->input('month', now()->format('Y-m'));
        
        $attendances = $employee->attendances()
            ->whereYear('date', '=', date('Y', strtotime($month)))
            ->whereMonth('date', '=', date('m', strtotime($month)))
            ->orderBy('date')
            ->get();

        $fileName = 'attendance_' . str_replace('-', '_', $month) . '.xlsx';
        
        return Excel::download(new AttendanceExport($attendances, $month), $fileName);
    }

    /**
     * Export employee's attendance history as PDF.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exportAttendancePdf(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        
        if (!$employee) {
            return redirect()->route('home')->with('error', 'Employee record not found.');
        }

        $month = $request->input('month', now()->format('Y-m'));
        
        $attendances = $employee->attendances()
            ->whereYear('date', '=', date('Y', strtotime($month)))
            ->whereMonth('date', '=', date('m', strtotime($month)))
            ->orderBy('date')
            ->get();
            
        $monthlySummary = $this->attendanceService->getMonthlySummary($employee->id, $month);
    
    // Get holiday count for the month
    $holidayCount = $attendances->where('status', 'Holiday')->count();
    $monthlySummary['holiday'] = $holidayCount;

    $pdf = PDF::loadView('attendance.exports.pdf', [
        'attendances' => $attendances,
        'month' => $month,
        'employee' => $employee,
        'monthlySummary' => $monthlySummary,
        'holidayCount' => $holidayCount
    ]);
        
        $fileName = 'attendance_' . str_replace('-', '_', $month) . '.pdf';
        
        return $pdf->download($fileName);
    }
}
