<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;

class AttendanceRegularizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employee = Auth::user()->employee;
        $is_company_admin = Auth::user()->hasRole('company_admin');

        // Company admin can see all requests
        if ($is_company_admin) {
            $pending_requests = AttendanceRegularization::where('status', '=', 'pending')
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10, ['*'], 'pending_page');

            $approved_requests = AttendanceRegularization::where('status', '=', 'approved')
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10, ['*'], 'approved_page');

            $rejected_requests = AttendanceRegularization::where('status', '=', 'rejected')
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10, ['*'], 'rejected_page');

            return view('employee.regularization.index', compact('pending_requests', 'approved_requests', 'rejected_requests'));
        }
        // Manager can see their team's requests
        elseif (is_null($employee->reporting_manager_id)) {
            $pending_requests = AttendanceRegularization::where('reporting_manager_id', $employee->id)
                ->where('status', '=', 'pending')
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10, ['*'], 'pending_page');

            $approved_requests = AttendanceRegularization::where('reporting_manager_id', $employee->id)
                ->where('status', '=', 'approved')
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10, ['*'], 'approved_page');

            $rejected_requests = AttendanceRegularization::where('reporting_manager_id', $employee->id)
                ->where('status', '=', 'rejected')
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10, ['*'], 'rejected_page');

            return view('employee.regularization.index', compact('pending_requests', 'approved_requests', 'rejected_requests'));
        } 
        // Regular employees can see their own requests
        else {
            $requests = $employee->attendanceRegularizations()
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10);
            return view('employee.regularization.index', compact('requests'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (is_null(Auth::user()->employee->reporting_manager_id)) {
            return redirect()->route('regularization.requests.index')->with('error', 'Managers cannot create regularization requests.');
        }
        return view('employee.regularization.create');
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

        $validator = Validator::make($request->all(), [
            'entries' => 'required|array|max:5',
            'entries.*.date' => 'required|date|before_or_equal:today',
            'entries.*.check_in' => 'nullable|date_format:H:i',
            'entries.*.check_out' => 'nullable|date_format:H:i',
            'entries.*.reason' => 'required|string',
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
        $is_company_admin = Auth::user()->hasRole('company_admin');
        
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
        $is_company_admin = Auth::user()->hasRole('company_admin');

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
