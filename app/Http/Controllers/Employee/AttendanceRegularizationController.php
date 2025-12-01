<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use App\Models\AttendanceSetting;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class AttendanceRegularizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $employee = Auth::user()->employee;
        $is_company_admin = Auth::user()->roles && Auth::user()->roles->contains('name', 'company_admin');

        // Get filter parameters
        $filters = [
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
            'status' => $request->get('status'),
            'employee_id' => $request->get('employee_id'),
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'search' => $request->get('search'),
        ];

        // Company admin can see all requests for their company
        if ($is_company_admin) {
            $pending_requests = $this->buildFilteredQuery($employee, 'pending', $filters, true);
            $approved_requests = $this->buildFilteredQuery($employee, 'approved', $filters, true);
            $rejected_requests = $this->buildFilteredQuery($employee, 'rejected', $filters, true);

            // Get employees for filter dropdown
            $employees = Employee::where('company_id', $employee->company_id)
                ->select('id', 'name', 'employee_code')
                ->orderBy('name')
                ->get();

            return view('employee.regularization.index', compact(
                'pending_requests',
                'approved_requests',
                'rejected_requests',
                'employees',
                'filters'
            ));
        }
        // Manager can see their team's requests
        elseif (is_null($employee->reporting_manager_id)) {
            $pending_requests = $this->buildFilteredQuery($employee, 'pending', $filters, false);
            $approved_requests = $this->buildFilteredQuery($employee, 'approved', $filters, false);
            $rejected_requests = $this->buildFilteredQuery($employee, 'rejected', $filters, false);

            // Get team employees for filter dropdown
            $employees = Employee::where('reporting_manager_id', $employee->id)
                ->select('id', 'name', 'employee_code')
                ->orderBy('name')
                ->get();

            return view('employee.regularization.index', compact(
                'pending_requests',
                'approved_requests',
                'rejected_requests',
                'employees',
                'filters'
            ));
        }
        // Regular employees can see their own requests
        else {
            $requests = $this->buildFilteredQueryForEmployee($employee, $filters);
            return view('employee.regularization.index', compact('requests', 'filters'));
        }
    }

    /**
     * Build filtered query for company admins and managers
     */
    private function buildFilteredQuery($employee, $status, $filters, $isCompanyAdmin = false)
    {
        $query = AttendanceRegularization::where('status', $status)
            ->with('employee', 'approver');

        if ($isCompanyAdmin) {
            $query->whereHas('employee', function($q) use ($employee) {
                $q->where('company_id', $employee->company_id);
            });
        } else {
            $query->where('reporting_manager_id', $employee->id);
        }

        // Apply filters
        $query = $this->applyCommonFilters($query, $filters);

        return $query->latest()->paginate(10, ['*'], $status . '_page');
    }

    /**
     * Build filtered query for regular employees
     */
    private function buildFilteredQueryForEmployee($employee, $filters)
    {
        $query = $employee->attendanceRegularizations()
            ->with('employee', 'approver');

        // Apply filters
        $query = $this->applyCommonFilters($query, $filters);

        return $query->latest()->paginate(10);
    }

    /**
     * Apply common filters to query
     */
    private function applyCommonFilters($query, $filters)
    {
        // Date range filter
        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $query->whereBetween('date', [
                Carbon::parse($filters['from_date'])->startOfDay(),
                Carbon::parse($filters['to_date'])->endOfDay()
            ]);
        }

        // Month and year filter
        if (!empty($filters['month']) && !empty($filters['year'])) {
            $query->whereMonth('date', $filters['month'])
                  ->whereYear('date', $filters['year']);
        } elseif (!empty($filters['month'])) {
            $query->whereMonth('date', $filters['month']);
        } elseif (!empty($filters['year'])) {
            $query->whereYear('date', $filters['year']);
        }

        // Employee filter (for managers/admins)
        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        // Search filter
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('reason', 'like', $searchTerm)
                  ->orWhereHas('employee', function($q) use ($searchTerm) {
                      $q->where('name', 'like', $searchTerm)
                        ->orWhere('employee_code', 'like', $searchTerm);
                  });
            });
        }

        return $query;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (is_null(Auth::user()->employee->reporting_manager_id)) {
            return redirect()->route('regularization.requests.index')->with('error', 'Managers cannot create regularization requests.');
        }

        $employee = Auth::user()->employee;
        $attendanceSettings = AttendanceSetting::where('company_id', $employee->company_id)->latest()->first();
        $officeStart = $attendanceSettings ? Carbon::createFromFormat('H:i:s', $attendanceSettings->office_start_time) : Carbon::createFromFormat('H:i:s', '09:00:00');
        $officeEnd = $attendanceSettings ? Carbon::createFromFormat('H:i:s', $attendanceSettings->office_end_time) : Carbon::createFromFormat('H:i:s', '18:00:00');
        $maxCheckout = $officeEnd->copy()->addHours(2);

        return view('employee.regularization.create', compact('officeStart', 'maxCheckout'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $employee = Auth::user()->employee;
        if (is_null($employee->reporting_manager_id)) {
            return redirect()->route('regularization.requests.index')->with('error', 'Managers cannot create regularization requests.');
        }

        // Get attendance settings for office timings
        $attendanceSettings = AttendanceSetting::where('company_id', $employee->company_id)->latest()->first();
        $officeStart = $attendanceSettings ? Carbon::createFromFormat('H:i:s', $attendanceSettings->office_start_time) : Carbon::createFromFormat('H:i:s', '09:00:00');
        $officeEnd = $attendanceSettings ? Carbon::createFromFormat('H:i:s', $attendanceSettings->office_end_time) : Carbon::createFromFormat('H:i:s', '18:00:00');
        $maxCheckout = $officeEnd->copy()->addHours(2);

        $validator = Validator::make($request->all(), [
            'entries' => 'required|array|max:5',
            'entries.*.date' => 'required|date|before:today',
            'entries.*.check_in' => 'required|date_format:H:i|after_or_equal:' . $officeStart->format('H:i').'|before_or_equal:' . $officeEnd->format('H:i'),
            'entries.*.check_out' => 'required|date_format:H:i|after_or_equal:entries.*.check_in|before_or_equal:' . $maxCheckout->format('H:i'),
            'entries.*.reason' => 'required|string',
        ], [
            'entries.*.check_in.required' => 'Check-in time is required.',
            'entries.*.date.before' => 'Date is required should be a day before today only.',
            'entries.*.check_out.required' => 'Check-out time is required.',
            'entries.*.check_in.before_or_equal' => 'Check-in time must be on or before ' . $officeEnd->format('H:i') . '.',
            'entries.*.check_out.after_or_equal' => 'Check-out time must be after check-in time.',
            'entries.max' => 'You can submit a maximum of 5 entries at a time.',
            'entries.*.date.before_or_equal' => 'Date cannot be in the future.',
            'entries.*.check_in.after_or_equal' => 'Time must be after or equal to :date.',
            'entries.*.check_out.before_or_equal' => 'Time must be before or equal to :date.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $dates = array_column($validated['entries'], 'date');
        if (count($dates) !== count(array_unique($dates))) {
            return redirect()->back()->with('error', 'Duplicate dates are not allowed in a single submission.')->withInput();
        }

        $batchId = Str::uuid();

        foreach ($validated['entries'] as $entry) {
            $employee->attendanceRegularizations()->create([
                'request_batch_id' => $batchId,
                'reporting_manager_id' => $employee->reporting_manager_id,
                'date' => $entry['date'],
                'check_in' => $entry['check_in'] ?? null,
                'check_out' => $entry['check_out'] ?? null,
                'reason' => $entry['reason'],
                'status' => 'pending',
            ]);
        }

        return redirect()->route('regularization.requests.index')
            ->with('success', 'Your regularization requests have been submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $request = AttendanceRegularization::with('employee', 'approver')->findOrFail($id);
        $employee = Auth::user()->employee;
        $requests = $employee->attendanceRegularizations()
            ->with('employee', 'approver')
            ->latest()
            ->paginate(10);

        return view('employee.regularization.show', compact('request', 'requests'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $request = AttendanceRegularization::findOrFail($id);
        // Add authorization logic here if needed

        return view('employee.regularization.edit', compact('request'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $regularizationRequest = AttendanceRegularization::findOrFail($id);
        $employee = Auth::user()->employee;
        $is_company_admin = Auth::user()->roles && Auth::user()->roles->contains('name', 'company_admin');

        // Check if user has permission to update this request
        if (!$is_company_admin && $regularizationRequest->reporting_manager_id !== $employee->id) {
            return redirect()->back()->with('error', 'You do not have permission to update this request.');
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
        ]);

        $validator->sometimes('attendance_status', 'required|in:Present,Late,Half Day', function ($input) {
            return $input->status == 'approved';
        });

        $validator->sometimes('reason', 'required|string|max:255', function ($input) {
            return $input->status == 'rejected';
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();

        $regularizationRequest->update([
            'status' => $validated['status'],
            'approved_by' => Auth::user()->employee->id,
        ]);

        if ($validated['status'] == 'approved') {
            $this->updateAttendanceFromRegularization($regularizationRequest, $validated['attendance_status']);
        }

        return redirect()->route('regularization.requests.index')
            ->with('success', 'Request has been ' . $validated['status'] . '.');
    }

    /**
     * Approve the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceRegularization $regularization)
    {
        $regularization->delete();

        return redirect()->route('regularization.requests.index')
            ->with('success', 'Request deleted successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $employee = Auth::user()->employee;
        $is_company_admin = Auth::user()->roles && Auth::user()->roles->contains('name', 'company_admin');

        $validator = Validator::make($request->all(), [
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:attendance_regularizations,id',
            'action' => 'required|in:approve,reject',
        ]);

        $validator->sometimes('attendance_status', 'required|in:Present,Late,Half Day', function ($input) {
            return $input->action == 'approve';
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        $status = ($validated['action'] == 'approve') ? 'approved' : 'rejected';

        $query = AttendanceRegularization::whereIn('id', $validated['request_ids'])
            ->where('status', 'pending');

        // If not company admin, only allow updating requests where user is reporting manager
        if (!$is_company_admin) {
            $query->where('reporting_manager_id', $employee->id);
        }

        $regularizations = $query->get();

        foreach ($regularizations as $regularization) {
            $regularization->update(['status' => $status, 'approved_by' => Auth::user()->employee->id]);

            if ($status == 'approved') {
                $this->updateAttendanceFromRegularization($regularization, $validated['attendance_status']);
            }
        }

        return redirect()->route('regularization.requests.index')
            ->with('success', 'Selected requests have been ' . $status . '.');
    }

    private function updateAttendanceFromRegularization(AttendanceRegularization $regularization, string $attendanceStatus)
    {
        $attendance = Attendance::updateOrCreate(
            [
                'employee_id' => $regularization->employee_id,
                'date' => $regularization->date,
            ],
            [
                'status' => $attendanceStatus,
                'check_in' => $regularization->check_in ? $regularization->date . ' ' . $regularization->check_in : null,
                'check_out' => $regularization->check_out ? $regularization->date . ' ' . $regularization->check_out : null,
                'updated_by' => Auth::user()->employee->id,
            ]
        );
    }
}
