<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\AcademicHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LeaveRequestController extends Controller
{
    /**
     * Display the admin calendar view.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminCalendar()
    {
        $companyId = Auth::user()->company_id;
        $departments = Department::where('company_id', $companyId)->get();
        return view('company.leave_requests.calendar', compact('departments'));
    }

    /**
     * Get leave requests as calendar events for admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function adminCalendarEvents(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $query = LeaveRequest::whereHas('employee', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->with(['employee.department', 'leaveType']);

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $leaveRequests = $query->get();

        return response()->json($leaveRequests->map(function ($request) {
            $statusColor = [
                'pending' => 'warning',
                'approved' => 'success',
                'rejected' => 'danger',
                'cancelled' => 'secondary'
            ][$request->status];

            return [
                'id' => $request->id,
                'title' => $request->employee->name . ' - ' . $request->leaveType->name,
                'start' => $request->start_date,
                'end' => Carbon::parse($request->end_date)->addDay()->format('Y-m-d'),
                'classNames' => ['fc-event-' . $request->status],
                'extendedProps' => [
                    'employeeName' => $request->employee->name,
                    'department' => $request->employee->department->name,
                    'leaveType' => $request->leaveType->name,
                    'status' => $request->status,
                    'statusColor' => $statusColor,
                    'startDate' => $request->start_date->format('Y-m-d'),
                    'endDate' => $request->end_date->format('Y-m-d'),
                    'totalDays' => $request->total_days,
                    'reason' => $request->reason,
                    'adminRemarks' => $request->admin_remarks,
                    'detailsUrl' => route('company.leave-requests.show', $request->id),
                    'approveUrl' => route('company.leave-requests.approve', $request->id),
                    'rejectUrl' => route('company.leave-requests.reject', $request->id)
                ]
            ];
        }));
    }

    /**
     * Display the employee calendar view.
     *
     * @return \Illuminate\Http\Response
     */
    public function employeeCalendar()
    {
        return view('employee.leave_requests.calendar');
    }

    /**
     * Get leave requests as calendar events for employee.
     *
     * @return \Illuminate\Http\Response
     */
    public function employeeCalendarEvents()
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->with(['leaveType'])
            ->get();

        return response()->json($leaveRequests->map(function ($request) {
            $statusColor = [
                'pending' => 'warning',
                'approved' => 'success',
                'rejected' => 'danger',
                'cancelled' => 'secondary'
            ][$request->status];

            return [
                'id' => $request->id,
                'title' => $request->leaveType->name,
                'start' => $request->start_date,
                'end' => Carbon::parse($request->end_date)->addDay()->format('Y-m-d'),
                'classNames' => ['fc-event-' . $request->status],
                'extendedProps' => [
                    'leaveType' => $request->leaveType->name,
                    'status' => $request->status,
                    'statusColor' => $statusColor,
                    'startDate' => $request->start_date->format('Y-m-d'),
                    'endDate' => $request->end_date->format('Y-m-d'),
                    'totalDays' => $request->total_days,
                    'reason' => $request->reason,
                    'adminRemarks' => $request->admin_remarks,
                    'detailsUrl' => route('employee.leave-requests.show', $request->id)
                ]
            ];
        }));
    }
    /**
     * Display a listing of the leave requests for admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminIndex(Request $request)
    {
        $companyId = Auth::user()->company_id;
        $query = LeaveRequest::whereHas('employee', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->with(['employee', 'leaveType', 'approver']);

        // Filter by department
        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('end_date', '<=', $request->date_to);
        }

        $leaveRequests = $query->latest()->get()->map(function ($request) {
            // Ensure working_days is properly cast to an array
            if (is_string($request->working_days)) {
                $request->working_days = json_decode($request->working_days, true) ?? [];
            } elseif (is_null($request->working_days)) {
                $request->working_days = [];
            }
            
            // Add working_days_count for the view
            $request->working_days_count = is_array($request->working_days) ? count($request->working_days) : 0;
            
            return $request;
        });
        
        $departments = Department::where('company_id', $companyId)->get();
        
        return view('company.leave_requests.index', compact('leaveRequests', 'departments'));
    }

    /**
     * Display a listing of the leave requests for employee.
     *
     * @return \Illuminate\Http\Response
     */
    public function employeeIndex()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)
            ->with(['leaveType'])
            ->latest()
            ->get()
            ->each(function ($request) {
                // Ensure working_days is properly cast to an array
                if (is_string($request->working_days)) {
                    $request->working_days = json_decode($request->working_days, true) ?? [];
                } elseif (is_null($request->working_days)) {
                    $request->working_days = [];
                }
                
                // Calculate working days count for display
                $request->working_days_count = is_array($request->working_days) ? count($request->working_days) : 0;
                
                return $request;
            });

        $currentYear = Carbon::now()->year;
        $leaveBalances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $currentYear)
            ->with('leaveType')
            ->get();
            
        return view('employee.leave_requests.index', compact('leaveRequests', 'leaveBalances'));
    }

    /**
     * Show the form for creating a new leave request.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            abort(404, 'Employee record not found.');
        }
        
        $leaveTypes = LeaveType::where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->get();
            
        $currentYear = Carbon::now()->year;
        
        // Get leave balances for the employee
        $leaveBalances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $currentYear)
            ->with('leaveType')
            ->get();
            
        return view('employee.leave_requests.create', compact('leaveTypes', 'leaveBalances'));
    }

    /**
     * Store a newly created leave request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * Check if a date is a weekend
     *
     * @param Carbon $date
     * @return bool
     */
    protected function isWeekend($date)
    {
        return $date->isWeekend();
    }

    /**
     * Check if a date is a holiday
     * 
     * @param Carbon $date
     * @param int $companyId
     * @return bool
     */
    protected function isHoliday($date, $companyId)
    {
        return AcademicHoliday::where('company_id', $companyId)
            ->whereDate('from_date', '<=', $date->format('Y-m-d'))
            ->whereDate('to_date', '>=', $date->format('Y-m-d'))
            ->exists();
    }

    /**
     * Calculate working days between two dates, excluding weekends and holidays
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $companyId
     * @return int
     */
    protected function calculateWorkingDays($startDate, $endDate, $companyId)
    {
        $workingDays = 0;
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            if (!$this->isWeekend($date) && !$this->isHoliday($date, $companyId)) {
                $workingDays++;
            }
        }
        
        return $workingDays;
    }

    public function store(Request $request)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        if (!$employee) {
            abort(404, 'Employee record not found.');
        }
        
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|max:2048', // 2MB max
        ]);
        
        // Check if leave type belongs to the company
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        if ($leaveType->company_id !== $employee->company_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Parse dates
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        
        // Get all dates in the leave period
        $period = CarbonPeriod::create($startDate, $endDate);
        
        $workingDays = [];
        $weekendDays = [];
        $holidayDates = [];
        
        // Categorize each day in the period
        foreach ($period as $date) {
            if ($this->isHoliday($date, $employee->company_id)) {
                $holidayDates[] = $date->format('Y-m-d');
            } elseif ($this->isWeekend($date)) {
                $weekendDays[] = $date->format('Y-m-d');
            } else {
                $workingDays[] = $date->format('Y-m-d');
            }
        }
        
        $totalWorkingDays = count($workingDays);
        $totalCalendarDays = $startDate->diffInDays($endDate) + 1;
        
        if ($totalWorkingDays <= 0) {
            return redirect()->back()
                ->with('error', 'The selected date range only includes weekends and/or holidays. No leave days will be deducted.')
                ->withInput();
        }
        
        // Check for overlapping approved or pending leaves
        if ($this->hasOverlappingLeaves($employee->id, $validated['start_date'], $validated['end_date'])) {
            return redirect()->back()->with('error', 'You already have an approved or pending leave request that overlaps with these dates.');
        }
        
        // Check leave balance
        $currentYear = Carbon::now()->year;
        $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $currentYear)
            ->first();
            
        if (!$leaveBalance) {
            return redirect()->back()
                ->with('error', 'No leave balance found for this leave type.')
                ->withInput();
        }
        
        $remainingDays = $leaveBalance->total_days - $leaveBalance->used_days;
        
        if ($totalWorkingDays > $remainingDays) {
            return redirect()->back()
                ->with('error', "Insufficient leave balance. You have only {$remainingDays} days remaining.")
                ->withInput();
        }
        
        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
        }
        
        // Create the leave request with the calculated values
        $leaveRequest = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalCalendarDays,
            'working_days' => $workingDays,
            'reason' => $validated['reason'],
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
        ]);
        
        return redirect()->route('employee.leave-requests.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Display the specified leave request.
     *
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return \Illuminate\Http\Response
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        // Check if user is authorized to view this leave request
        if ($user->role === 'admin') {
            // Admin can only view leave requests from their company
            if ($leaveRequest->employee->company_id !== $user->company_id) {
                abort(403, 'Unauthorized action.');
            }
        } elseif ($leaveRequest->employee_id !== $employee->id) {
            // Employee can only view their own leave requests
            abort(403, 'Unauthorized action.');
        }
        
        // Load the leave request with its relationships
        $leaveRequest->load(['employee', 'leaveType', 'approver']);
        
        // Get holidays during the leave period
        $holidays = $this->getHolidaysInPeriod(
            $leaveRequest->start_date, 
            $leaveRequest->end_date, 
            $leaveRequest->employee->company_id
        );
        
        // Get all dates in the leave period
        $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);
        
        $workingDays = [];
        $weekendDays = [];
        $holidayDates = [];
        
        // Categorize each day in the period
        foreach ($period as $date) {
            if ($this->isHoliday($date, $leaveRequest->employee->company_id)) {
                $holidayDates[] = $date->format('Y-m-d');
            } elseif ($this->isWeekend($date)) {
                $weekendDays[] = $date->format('Y-m-d');
            } else {
                $workingDays[] = $date->format('Y-m-d');
            }
        }
        
        $totalCalendarDays = $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1;
        
        // Get approved working days from the leave request
        $approvedWorkingDays = is_array($leaveRequest->working_days) ? $leaveRequest->working_days : [];

        // Return appropriate view based on user role
        $viewPath = $user->role === 'admin' ? 'company.leave_requests.show' : 'employee.leave_requests.show';
        return view($viewPath, [
            'leaveRequest' => $leaveRequest,
            'holidays' => $holidays,
            'workingDays' => $workingDays,
            'totalCalendarDays' => $totalCalendarDays,
            'weekendDays' => $weekendDays,
            'holidayDates' => $holidayDates,
            'approvedWorkingDays' => $approvedWorkingDays
        ]);
    }
    
    /**
     * Get holidays within a date range
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $companyId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getHolidaysInPeriod($startDate, $endDate, $companyId)
    {
        return AcademicHoliday::where('company_id', $companyId)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('from_date', [$startDate, $endDate])
                    ->orWhereBetween('to_date', [$startDate, $endDate])
                    ->orWhere(function($q) use ($startDate, $endDate) {
                        $q->where('from_date', '<=', $startDate)
                            ->where('to_date', '>=', $endDate);
                    });
            })
            ->orderBy('from_date')
            ->get();
    }
    


    /**
     * Show the form for editing the specified leave request.
     *
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        // Check if user is authorized to edit this leave request
        if (!$employee || $leaveRequest->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if leave request is still pending
        if ($leaveRequest->status !== 'pending') {
            return redirect()->route('employee.leave-requests.index')
                ->with('error', 'Only pending leave requests can be edited.');
        }
        
        $leaveTypes = LeaveType::where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->get();
            
        $currentYear = Carbon::now()->year;
        
        // Get leave balances for the employee
        $leaveBalances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $currentYear)
            ->with('leaveType')
            ->get();
            
        return view('employee.leave_requests.edit', compact('leaveRequest', 'leaveTypes', 'leaveBalances'));
    }

    /**
     * Update the specified leave request in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            abort(404, 'Employee record not found.');
        }
        
        // Check if user is authorized to update this leave request
        if ($leaveRequest->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if leave request is still pending
        if ($leaveRequest->status !== 'pending') {
            return redirect()->route('employee.leave-requests.index')
                ->with('error', 'Only pending leave requests can be updated.');
        }
        
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|max:2048', // 2MB max
        ]);
        
        // Check for overlapping approved or pending leaves (excluding current leave request)
        if ($this->hasOverlappingLeaves($employee->id, $validated['start_date'], $validated['end_date'], $leaveRequest->id)) {
            return redirect()->back()->with('error', 'You already have another approved or pending leave request that overlaps with these dates.');
        }
        
        // Check if leave type belongs to the company
        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);
        if ($leaveType->company_id !== $employee->company_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Calculate total days - ensure start date is before end date
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        
        // Ensure start date is before or equal to end date
        if ($startDate->gt($endDate)) {
            return redirect()->back()->with('error', 'End date must be after or equal to start date.');
        }
        
        $totalDays = $startDate->diffInDays($endDate) + 1;
        
        // Check leave balance
        $currentYear = Carbon::now()->year;
        $leaveBalance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', $currentYear)
            ->first();
            
        if (!$leaveBalance) {
            return redirect()->back()->with('error', 'No leave balance found for this leave type.');
        }
        
        // If leave type changed, check balance
        if ($leaveRequest->leave_type_id != $validated['leave_type_id']) {
            $remainingDays = $leaveBalance->total_days - $leaveBalance->used_days;
            
            if ($totalDays > $remainingDays) {
                return redirect()->back()->with('error', "Insufficient leave balance. You have only {$remainingDays} days remaining.");
            }
        } else {
            // If same leave type but days changed
            $additionalDays = $totalDays - $leaveRequest->total_days;
            $remainingDays = $leaveBalance->total_days - $leaveBalance->used_days;
            
            if ($additionalDays > $remainingDays) {
                return redirect()->back()->with('error', "Insufficient leave balance. You have only {$remainingDays} days remaining.");
            }
        }
        
        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($leaveRequest->attachment_path) {
                Storage::disk('public')->delete($leaveRequest->attachment_path);
            }
            
            $attachmentPath = $request->file('attachment')->store('leave-attachments', 'public');
            $validated['attachment_path'] = $attachmentPath;
        }
        
        // Update leave request
        $validated['total_days'] = $totalDays;
        $leaveRequest->update($validated);
        
        return redirect()->route('employee.leave-requests.index')
            ->with('success', 'Leave request updated successfully.');
    }

    /**
     * Cancel the specified leave request.
     *
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return \Illuminate\Http\Response
     */
    public function cancel(LeaveRequest $leaveRequest)
    {
        $employee = Employee::where('user_id', Auth::id())->first();
        
        // Check if user is authorized to cancel this leave request
        if (!$employee || $leaveRequest->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if leave request is still pending
        if ($leaveRequest->status !== 'pending') {
            return redirect()->route('employee.leave-requests.index')
                ->with('error', 'Only pending leave requests can be cancelled.');
        }
        
        $leaveRequest->update(['status' => 'cancelled']);
        
        return redirect()->route('employee.leave-requests.index')
            ->with('success', 'Leave request cancelled successfully.');
    }

    /**
     * Approve the specified leave request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return \Illuminate\Http\Response
     */
    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        // Check if user is admin or company admin
        if (!in_array(Auth::user()->role, ['admin', 'company_admin'])) {
            abort(403, 'Unauthorized action. Only administrators can approve leave requests.');
        }
        
        // Check if leave request belongs to an employee in the company
        if ($leaveRequest->employee->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if leave request is pending
        if ($leaveRequest->status !== 'pending') {
            return redirect()->route('company.leave-requests.index')
                ->with('error', 'Only pending leave requests can be approved.');
        }
        
        $validated = $request->validate([
            'admin_remarks' => 'nullable|string',
        ]);
        
        // Update leave balance with working days count instead of total days
        $workingDaysCount = is_array($leaveRequest->working_days) ? count($leaveRequest->working_days) : 0;
        
        $leaveBalance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', Carbon::parse($leaveRequest->start_date)->year)
            ->first();
            
        if ($leaveBalance) {
            $leaveBalance->update([
                'used_days' => $leaveBalance->used_days + $workingDaysCount,
            ]);
        }
        
        // Approve leave request
        $leaveRequest->update([
            'status' => 'approved',
            'admin_remarks' => $validated['admin_remarks'] ?? null,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        
        return redirect()->route('company.leave-requests.index')
            ->with('success', 'Leave request approved successfully.');
    }

    /**
     * Reject the specified leave request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        // Check if user is admin or company admin
        if (!in_array(Auth::user()->role, ['admin', 'company_admin'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action. Only administrators can reject leave requests.'], 403);
            }
            abort(403, 'Unauthorized action. Only administrators can reject leave requests.');
        }
        
        // Check if leave request belongs to an employee in the company
        if ($leaveRequest->employee->company_id !== Auth::user()->company_id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }
        
        // Check if leave request is pending
        if ($leaveRequest->status !== 'pending') {
            $message = 'Only pending leave requests can be rejected.';
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return redirect()->route('company.leave-requests.index')
                ->with('error', $message);
        }
        
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);
        
        try {
            // Reject leave request
            $leaveRequest->update([
                'status' => 'rejected',
                'admin_remarks' => $validated['rejection_reason'],
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Leave request rejected successfully.'
                ]);
            }
            
            return redirect()->route('company.leave-requests.index')
                ->with('success', 'Leave request rejected successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Error rejecting leave request: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'An error occurred while rejecting the leave request.'
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'An error occurred while rejecting the leave request.');
        }
    }

    /**
     * Check if there are any overlapping leave requests for the given employee and date range.
     *
     * @param  int  $employeeId
     * @param  string  $startDate
     * @param  string  $endDate
     * @param  int|null  $excludeLeaveRequestId
     * @return bool
     */
    protected function hasOverlappingLeaves($employeeId, $startDate, $endDate, $excludeLeaveRequestId = null)
    {
        $query = LeaveRequest::where('employee_id', $employeeId)
            ->whereIn('status', ['approved', 'pending'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($innerQ) use ($startDate, $endDate) {
                      $innerQ->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                  });
            });
            
        if ($excludeLeaveRequestId) {
            $query->where('id', '!=', $excludeLeaveRequestId);
        }
        
        return $query->exists();
    }
    
    /**
     * Display the specified leave request for admin.
     *
     * @param  \App\Models\LeaveRequest  $leaveRequest
     * @return \Illuminate\Http\Response
     */
    public function adminShow(LeaveRequest $leaveRequest)
    {
        // Check if user is admin or company admin
        if (!in_array(Auth::user()->role, ['admin', 'company_admin'])) {
            abort(403, 'Unauthorized action. Only administrators can view leave request details.');
        }
        
        // Check if leave request belongs to an employee in the company
        if ($leaveRequest->employee->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Load necessary relationships
        $leaveRequest->load(['employee.department', 'leaveType', 'approver']);

        // Get leave balance for this leave type
        $leaveBalance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', Carbon::parse($leaveRequest->start_date)->year)
            ->first();
            
        // Get holidays during the leave period
        $holidays = $this->getHolidaysInPeriod(
            $leaveRequest->start_date, 
            $leaveRequest->end_date, 
            $leaveRequest->employee->company_id
        );
        
        // Get all dates in the leave period
        $period = CarbonPeriod::create($leaveRequest->start_date, $leaveRequest->end_date);
        
        $workingDays = [];
        $weekendDays = [];
        $holidayDates = [];
        
        // Categorize each day in the period
        foreach ($period as $date) {
            if ($this->isHoliday($date, $leaveRequest->employee->company_id)) {
                $holidayDates[] = $date->format('Y-m-d');
            } elseif ($this->isWeekend($date)) {
                $weekendDays[] = $date->format('Y-m-d');
            } else {
                $workingDays[] = $date->format('Y-m-d');
            }
        }
        
        // Get approved working days from the leave request
        $approvedWorkingDays = is_array($leaveRequest->working_days) ? $leaveRequest->working_days : [];
        
        return view('company.leave_requests.show', [
            'leaveRequest' => $leaveRequest,
            'leaveBalance' => $leaveBalance,
            'holidays' => $holidays,
            'workingDays' => $workingDays,
            'weekendDays' => $weekendDays,
            'holidayDates' => $holidayDates,
            'approvedWorkingDays' => $approvedWorkingDays,
            'totalCalendarDays' => $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1
        ]);
    }
}
