<?php

namespace App\Http\Controllers;

use App\Models\AcademicHoliday;
use App\Models\Company; // Added
use App\Models\EmployeeResignation;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Reimbursement;
use App\Models\User;    // Added
use App\Models\Department; // Added
use App\Models\Attendance; // Added
use Carbon\Carbon; // Added for date handling
use Illuminate\Http\Request;
use App\Models\AttendanceRegularization;
use App\Models\Employee;
use Illuminate\Support\Facades\DB; // Added for role breakdown
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    // public function index()
    // {
    //     $totalCompanies = Company::count();
    //     $totalUsers = User::count();
    //     $totalDepartments = Department::count();

    //     $usersByRole = User::select('role', DB::raw('count(*) as total'))
    //                         ->groupBy('role')
    //                         ->pluck('total', 'role');

    //     // Fetch companies with their admin users
    //     // Eager load the 'admin' relationship to avoid N+1 queries
    //     $companiesWithAdmins = Company::with('admin')->get();

    //     // Get the authenticated user
    //     $loggedInUser = auth()->user();

    //     return view('home', compact(
    //         'totalCompanies',
    //         'totalUsers',
    //         'totalDepartments',
    //         'usersByRole',
    //         'companiesWithAdmins', // Add this to the compact function
    //         'loggedInUser'         // Add this to the compact function
    //     ));
    // }

    public function index()
    {
        $user = Auth::user();
        $loggedInUser = $user;

        // Check if user wants to switch to employee dashboard
        $dashboardMode = session('dashboard_mode', 'default');

        $employeeView = false;
        // For admin users, check if they want to view as employee
        if (in_array($user->role, ['admin']) && $dashboardMode === 'employee') {
            $employeeView = true;
        }

        // Common data for all roles
        $employeeRoles = User::whereNotNull('role')
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->orderBy('total', 'desc')
            ->get();

        // Prepare data for charts
        $roleLabels = $employeeRoles->pluck('role');
        $roleData = $employeeRoles->pluck('total');
        $roleColors = [
            '#4e73df',
            '#1cc88a',
            '#36b9cc',
            '#f6c23e',
            '#e74a3b',
            '#5a5c69',
            '#858796',
            '#e83e8c',
            '#fd7e14',
            '#20c9a6'
        ];

        // Sample upcoming holidays (replace with actual data from your database)
        $upcomingHolidays = [
            [
                'name' => 'New Year\'s Day',
                'date' => now()->addDays(5)->format('Y-m-d'),
                'type' => 'Public Holiday'
            ],
            [
                'name' => 'Republic Day',
                'date' => now()->addDays(15)->format('Y-m-d'),
                'type' => 'Public Holiday'
            ],
            [
                'name' => 'Company Foundation Day',
                'date' => now()->addDays(30)->format('Y-m-d'),
                'type' => 'Company Holiday'
            ]
        ];

        if ($user->role === 'superadmin') {
            $totalCompanies = Company::count();
            $totalUsers = User::count();
            $totalDepartments = Department::count();
            $usersByRole = User::select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->pluck('total', 'role');
            $companiesWithAdmins = Company::with('admin')->get();

            return view('home', compact(
                'totalCompanies',
                'totalUsers',
                'totalDepartments',
                'usersByRole',
                'companiesWithAdmins',
                'loggedInUser',
                'roleLabels',
                'roleData',
                'roleColors'
            ));
        } elseif ($user->role === 'company_admin') {
            // For company admin, show data for their company only
            $companyId = $user->company_id;

            // Get employee distribution by role
            $companyEmployees = User::where('company_id', $companyId)
                ->select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->orderBy('total', 'desc')
                ->get();


            // Get department count
            $departmentCount = Department::where('company_id', $companyId)->count();

            // Get today's attendance
            $today = now()->format('Y-m-d');
            $todayAttendanceCount = DB::table('attendances as a')
                ->join('employees as e', 'a.employee_id', '=', 'e.id')
                ->whereNull('a.deleted_at')
                ->whereDate('a.created_at', $today)
                ->where('e.company_id', $companyId)
                ->count();

            $totalEmployees = $companyEmployees->sum('total');

            // Get pending leave requests count
            $pendingLeaves = \App\Models\LeaveRequest::whereHas('employee', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
                ->where('status', 'pending')
                ->count();

            // Sample recent activities (replace with actual activities from your system)
            $recentActivities = [
                [
                    'title' => 'New employee onboarded',
                    'time' => '2 hours ago',
                    'description' => 'John Doe joined the Marketing team'
                ],
                [
                    'title' => 'Leave request approved',
                    'time' => '5 hours ago',
                    'description' => 'Approved leave request for Jane Smith'
                ],
                [
                    'title' => 'New department created',
                    'time' => '1 day ago',
                    'description' => 'Created new department: Product Development'
                ],
                [
                    'title' => 'Performance review completed',
                    'time' => '2 days ago',
                    'description' => 'Completed Q2 performance reviews for Sales team'
                ],
                [
                    'title' => 'Training session scheduled',
                    'time' => '3 days ago',
                    'description' => 'Scheduled customer service training for next week'
                ]
            ];

            //Presentee_count
            $presentees_count = Attendance::whereDate('date', Carbon::today())
                ->count();

            $companyRoleLabels = $companyEmployees->pluck('role');
            $companyRoleData = $companyEmployees->pluck('total');

            $companyId = $user->employee->company_id ?? null;

            $announcements = Announcement::where('company_id', $companyId)
                ->whereIn('audience', ['admins', 'employees', 'both'])
                ->latest()
                ->get();

            //Absentees data

            $today = Carbon::today();
            $total_employees = Employee::select('id', 'name', 'department_id')->with('department')->get();
            $present_employees = Attendance::whereDate('date', $today)
                ->pluck('employee_id')
                ->toArray();

            $absentees = $total_employees->filter(function ($employee) use ($present_employees) {
                return !in_array($employee->id, $present_employees);
            })->values();
            $absentees_count = $absentees->count();
            $absentees = $absentees->take(5); // Limit to 5 absentees for display

            //ClockIn data

            $attendances = Attendance::with([
                'employee' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
                ->select('id', 'employee_id', 'date', 'status')
                ->whereDate('date', $today)
                ->get();

            $newJoineesCount = Employee::whereMonth('joining_date', Carbon::now()->month)
                ->whereYear('joining_date', Carbon::now()->year)
                ->count();



            $companyID = auth()->user()->company_id;

            $departments = Department::where('company_id', $companyID)
                ->withCount('employees')
                ->get();

            $labels = $departments->pluck('name');
            // dd($labels);
            $data = $departments->pluck('employees_count');
            $colors = ['#ffcd56', '#ff6384', '#4bc0c0', '#36a2eb', '#9966ff', '#ff9f40'];

            $academic_holidays = AcademicHoliday::where('company_id', $companyID)
                ->whereDate('from_date', '>=', Carbon::today())
                ->orderBy('from_date', 'asc')
                ->take(5)
                ->get();

            $reimbursements = Reimbursement::where('company_id', $companyID)
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            $pending_regularization_requests = AttendanceRegularization::where('reporting_manager_id', $user->employee->id)
                ->where('status', '=', 'pending')
                ->with('employee', 'approver')
                ->latest()
                ->limit(5)
                ->get();

            // Attendance status counts for today
            $todaysAttendance = Attendance::whereDate('date', Carbon::today())
                ->with('employee')
                ->select('id', 'employee_id', 'date', 'status')
                ->get();

            $attendanceCounts = [
                'Present' => $todaysAttendance->where('status', 'Present')->count(),
                'Absent' => $todaysAttendance->where('status', 'Absent')->count(),
                'On Leave' => $todaysAttendance->where('status', 'On Leave')->count(),
                'Half Day' => $todaysAttendance->where('status', 'Half Day')->count(),
                'Late' => $todaysAttendance->where('status', 'Late')->count(),
            ];

            //upcoming birthday
            $employee = auth()->user()->employee;
            $now = Carbon::now();
            $upcoming_birthday = Employee::where('company_id', $employee->company_id)
                ->where(function ($q) use ($now) {
                    $q->where(function ($q2) use ($now) {
                        $q2->whereMonth('dob', $now->month)
                            ->whereDay('dob', '>=', $now->day);
                    })->orWhere(function ($q2) use ($now) {
                        $q2->whereMonth('dob', '>', $now->month);
                    });
                })
                ->orderByRaw("MONTH(dob), DAY(dob)")
                ->select('dob', 'name')
                ->first();

            if (!$upcoming_birthday) {
                $upcoming_birthday = Employee::where('company_id', $employee->company_id)
                    ->orderByRaw("MONTH(dob), DAY(dob)")
                    ->select('dob', 'name')
                    ->first();
            }

            $resignedEmployeesCount = EmployeeResignation::where('company_id', $companyID)
                ->where('status', 'approved')
                ->whereNotNull('resignation_date')
                ->whereMonth('resignation_date', Carbon::now()->month)
                ->count();

            return view('company_admin.dashboard', [
                'labels' => $labels,
                'data' => $data,
                'colors' => $colors,
            ], compact(
                'roleLabels',
                'roleData',
                'announcements',
                'roleColors',
                'resignedEmployeesCount',
                'pending_regularization_requests',
                'reimbursements',
                'newJoineesCount',
                'academic_holidays',
                'attendances',
                'attendanceCounts',
                'absentees',
                'absentees_count',
                'presentees_count',
                'companyRoleLabels',
                'upcoming_birthday',
                'companyRoleData',
                'departmentCount',
                'todayAttendanceCount',
                'totalEmployees',
                'recentActivities',
                'pendingLeaves',
                'upcomingHolidays'
            ));
        } elseif ($user->role === 'admin' && !$employeeView) {
            // Get total employees in the company
            // Get employee distribution by role
            $companyId = $user->company_id;
            $companyEmployees = User::where('company_id', $companyId)
                ->select('role', DB::raw('count(*) as total'))
                ->groupBy('role')
                ->orderBy('total', 'desc')
                ->get();
            $totalEmployees = User::where('company_id', $user->company_id)
                ->where('role', '!=', 'superadmin')
                ->where('role', '!=', 'company_admin')
                ->count();

            // Get department data for the company
            $departments = Department::where('company_id', $user->company_id)
                ->withCount('employees')
                ->orderBy('employees_count', 'desc')
                ->get();

            $departmentNames = $departments->pluck('name')->toArray();
            $departmentCounts = $departments->pluck('employees_count')->toArray();

            $departmentData = [
                'names' => $departmentNames,
                'counts' => $departmentCounts
            ];

            // Get today's attendance count
            $today = now()->format('Y-m-d');
            $todayAttendanceCount = DB::table('attendances as a')
                ->join('employees as e', 'a.employee_id', '=', 'e.id')
                ->whereDate('a.created_at', $today)
                ->whereNull('a.deleted_at')
                ->where('e.company_id', $user->company_id)
                ->count();

            // Get employees on leave today
            $onLeaveCount = \App\Models\LeaveRequest::whereHas('employee', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->where('status', 'approved')
                ->count();

            // Get pending leave requests count
            $pendingRequests = \App\Models\LeaveRequest::whereHas('employee', function ($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })
                ->where('status', 'pending')
                ->count();


            $announcements = Announcement::where('company_id', $user->employee->company_id)
                ->whereIn('audience', ['admins', 'both'])
                ->latest()
                ->get();

            $academic_holidays = AcademicHoliday::where('company_id', $user->company_id)
                ->whereDate('from_date', '>=', Carbon::today())
                ->orderBy('from_date', 'asc')
                ->take(5)
                ->get();

            //Absentees data

            $today = Carbon::today();
            $total_employees = Employee::select('id', 'name', 'department_id')->with('department')->get();
            $present_employees = Attendance::whereDate('date', $today)
                ->pluck('employee_id')
                ->toArray();

            $absentees = $total_employees->filter(function ($employee) use ($present_employees) {
                return !in_array($employee->id, $present_employees);
            })->values();

            $absentees_count = $absentees->count();

            //pending regularization requests

            $pending_regularization_requests = AttendanceRegularization::where('reporting_manager_id', $user->employee->id)
                ->where('status', '=', 'pending')
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10, ['*'], 'pending_page');

            // Attendance status counts for today
            $attendances = Attendance::with([
                'employee' => function ($query) {
                    $query->select('id', 'name');
                }
            ])
                ->select('id', 'employee_id', 'date', 'status')
                ->whereDate('date', $today)
                ->get();

            // Get today's attendance
            $today = now()->format('Y-m-d');
            $todayAttendanceCount = DB::table('attendances as a')
                ->join('employees as e', 'a.employee_id', '=', 'e.id')
                ->whereNull('a.deleted_at')
                ->whereDate('a.created_at', $today)
                ->where('e.company_id', $companyId)
                ->count();

            $totalEmployees = $companyEmployees->sum('total');

            // Attendance status counts for today
            $todaysAttendance = Attendance::whereDate('date', Carbon::today())
                ->with('employee')
                ->select('id', 'employee_id', 'date', 'status')
                ->get();


            $attendanceCounts = [
                'Present' => $todaysAttendance->where('status', 'Present')->count(),
                'Absent' => $todaysAttendance->where('status', 'Absent')->count(),
                'On Leave' => $todaysAttendance->where('status', 'On Leave')->count(),
                'Half Day' => $todaysAttendance->where('status', 'Half Day')->count(),
                'Late' => $todaysAttendance->where('status', 'Late')->count(),
            ];

            $departments = Department::where('company_id', $companyId)
                ->withCount('employees')
                ->get();

            $labels = $departments->pluck('name');
            $data = $departments->pluck('employees_count');
            $colors = ['#ffcd56', '#ff6384', '#4bc0c0', '#36a2eb', '#9966ff', '#ff9f40'];

            //upcoming_birthday
            $employee = auth()->user()->employee;
            $now = Carbon::now();
            $upcoming_birthday = Employee::where('company_id', $employee->company_id)
                ->where(function ($q) use ($now) {
                    $q->where(function ($q2) use ($now) {
                        $q2->whereMonth('dob', $now->month)
                            ->whereDay('dob', '>=', $now->day);
                    })->orWhere(function ($q2) use ($now) {
                        $q2->whereMonth('dob', '>', $now->month);
                    });
                })
                ->orderByRaw("MONTH(dob), DAY(dob)")
                ->select('dob', 'name')
                ->first();

            if (!$upcoming_birthday) {
                $upcoming_birthday = Employee::where('company_id', $employee->company_id)
                    ->orderByRaw("MONTH(dob), DAY(dob)")
                    ->select('dob', 'name')
                    ->first();
            }

            return view('admin.dashboard', [
                'totalEmployees' => $totalEmployees,
                'departmentCount' => count($departmentData['names']),
                'departmentData' => $departmentData,
                'todayAttendanceCount' => $todayAttendanceCount,
                'onLeaveCount' => $onLeaveCount,
                'absentees' => $absentees,
                'pendingRequests' => $pendingRequests,
                'roleLabels' => $roleLabels,
                'roleData' => $roleData,
                'pending_regularization_requests' => $pending_regularization_requests,
                'roleColors' => $roleColors,
                'announcements' => $announcements,
                'academic_holidays' => $academic_holidays,
                'attendances' => $attendances,
                'attendanceCounts' => $attendanceCounts,
                'labels' => $labels,
                'data' => $data,
                'colors' => $colors,
                'upcoming_birthday' => $upcoming_birthday,
            ]);
        } elseif ($user->role === 'employee' || $employeeView) {
            // Employee dashboard
            $employee = $user->employee;
            if (!$employee) {
                return redirect()->route('home')->with('error', 'Employee record not found.');
            }

            // Get today's attendance
            $todayAttendance = $employee->attendances()
                ->whereDate('date', now()->toDateString())
                ->first();

            // $attendanceChartData = $employee->attendances();

            // $currentYear = Carbon::now()->year;
            // $attendance = Attendance::where('employee_id', $employee->id)
            //     ->whereYear('date', $currentYear)
            //     ->get();

            // $present_count = $attendance->where('status', 'Present')->count();
            // $absent_count = $attendance->where('status', 'Absent')->count();
            // $leave_count = $attendance->where('status', 'On Leave')->count();
            // $late_count = $attendance->where('status', 'Late')->count();
            // $half_day_count = $attendance->where('status', 'Half Day')->count();

            // Calculate total available leave balance
            $leaveBalance = 0;
            $currentYear = now()->year;

            // Get all leave balances for the current year
            $leaveBalances = $employee->leaveBalances()
                ->where('year', $currentYear)
                ->get();

            $academic_holidays = AcademicHoliday::where('company_id', $employee->company_id)
                ->whereDate('from_date', '>=', Carbon::today())
                ->orderBy('from_date', 'asc')
                ->take(5)
                ->get();

            // Sum up all available leave days (total_days - used_days)
            foreach ($leaveBalances as $balance) {
                $leaveBalance += ($balance->total_days - $balance->used_days);
            }

            $employee = auth()->user()->employee;
            $currentYear = Carbon::now()->year;

            // Group attendance by month and status
            $attendanceData = Attendance::where('employee_id', $employee->id)
                ->whereYear('date', $currentYear)
                ->get()
                ->groupBy(function ($attendance) {
                    return Carbon::parse($attendance->date)->format('m'); // group by month number (01–12)
                });

            // Prepare arrays for Chart.js
            $monthlyData = [
                'Present' => array_fill(1, 12, 0),
                'Absent' => array_fill(1, 12, 0),
                'Late' => array_fill(1, 12, 0),
                'On Leave' => array_fill(1, 12, 0),
                'Half Day' => array_fill(1, 12, 0),
            ];


            foreach ($attendanceData as $month => $records) {
                $monthlyData['Present'][(int) $month] = $records->where('status', 'Present')->count();
                $monthlyData['Absent'][(int) $month] = $records->where('status', 'Absent')->count();
                $monthlyData['Late'][(int) $month] = $records->where('status', 'Late')->count();
                $monthlyData['On Leave'][(int) $month] = $records->where('status', 'On Leave')->count();
                $monthlyData['Half Day'][(int) $month] = $records->where('status', 'Half Day')->count();
            }

            // dd(Carbon::now()->format('Y-m-d'));

            $now = Carbon::now();
            $upcoming_birthday = Employee::where('company_id', $employee->company_id)
                ->where(function ($q) use ($now) {
                    $q->where(function ($q2) use ($now) {
                        $q2->whereMonth('dob', $now->month)
                            ->whereDay('dob', '>=', $now->day);
                    })->orWhere(function ($q2) use ($now) {
                        $q2->whereMonth('dob', '>', $now->month);
                    });
                })
                ->orderByRaw("MONTH(dob), DAY(dob)")
                ->select('dob', 'name')
                ->first();

            if (!$upcoming_birthday) {
                $upcoming_birthday = Employee::where('company_id', $employee->company_id)
                    ->orderByRaw("MONTH(dob), DAY(dob)")
                    ->select('dob', 'name')
                    ->first();
            }

            $total_leaves = LeaveBalance::where('employee_id', $employee->id)
                ->select('total_days')
                ->sum('total_days');

            $leaves_taken = LeaveBalance::where('employee_id', $employee->id)
                ->whereYear('year', $currentYear)
                ->select('used_days')
                ->sum('used_days');

            $leave_balance = $total_leaves - $leaves_taken;

            $monthlyLeavesTaken = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthlyLeavesTaken[] = LeaveRequest::where('employee_id', $employee->id)
                    ->whereYear('start_date', $currentYear)
                    ->whereMonth('start_date', $m)
                    ->where('status', 'approved')
                    ->sum('total_days');
            }

            $announcements = Announcement::where('company_id', $user->employee->company_id)
                ->whereIn('audience', ['employees', 'both'])
                ->latest()
                ->get();


            return view('employee.dashboard', compact(
                'loggedInUser',
                'todayAttendance',
                'announcements',
                'monthlyData',
                'monthlyLeavesTaken',
                'leaves_taken',
                'leave_balance',
                'currentYear',
                'leaveBalance',
                'upcoming_birthday',
                'academic_holidays'
            ));
        } elseif ($user->role === 'user') {
            return view('user.dashboard', compact('loggedInUser'));
        } else {
            abort(403, 'Unauthorized action.');
        }

    }

    /**
     * Switch dashboard mode for admin users
     */
    public function switchDashboard(Request $request)
    {
        $user = Auth::user();

        // Only allow admin and company_admin to switch dashboards
        if (!in_array($user->role, ['admin'])) {
            return redirect()->route('home')->with('error', 'You do not have permission to switch dashboards.');
        }

        $mode = $request->input('mode', 'default');

        // Validate mode
        if (!in_array($mode, ['default', 'employee'])) {
            return redirect()->route('home')->with('error', 'Invalid dashboard mode.');
        }

        // Set the dashboard mode in session
        session(['dashboard_mode' => $mode]);

        $message = $mode === 'employee' ? 'Switched to Employee Dashboard' : 'Switched to Admin Dashboard';

        return redirect()->route('home')->with('success', $message);
    }

    public function blank()
    {
        return view('layouts.blank-page');
    }
}
