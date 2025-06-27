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

class AttendanceRegularizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employee = Auth::user()->employee;

        if (is_null($employee->reporting_manager_id)) {
            $requests = AttendanceRegularization::where('reporting_manager_id', $employee->id)
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10);
        } else {
            $requests = $employee->attendanceRegularizations()
                ->with('employee', 'approver')
                ->latest()
                ->paginate(10);
        }

        return view('employee.regularization.index', compact('requests'));
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
        // Add authorization logic here if needed

        return view('employee.regularization.show', compact('request'));
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
        // Add authorization logic here if needed

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'nullable|string|max:255', // For rejection reason
        ]);

        $regularizationRequest->update([
            'status' => $request->status,
            'approved_by' => Auth::user()->employee->id,
        ]);

        return redirect()->route('regularization.requests.index')
            ->with('success', 'Request has been ' . $request->status);
    }

    /**
     * Approve the specified resource in storage.
     */
    public function approve(Request $request, $id)
    {
        $regularization = AttendanceRegularization::findOrFail($id);
        // Add authorization logic here if needed

        $regularization->update([
            'status' => 'approved',
            'approved_by' => Auth::user()->employee->id,
        ]);

        $this->updateAttendanceFromRegularization($regularization);

        return redirect()->route('regularization.requests.index')
            ->with('success', 'Request approved successfully.');
    }

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
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:attendance_regularizations,id',
            'action' => 'required|in:approve,reject',
        ]);

        $status = ($request->action == 'approve') ? 'approved' : 'rejected';

        $regularizations = AttendanceRegularization::whereIn('id', $request->request_ids)
            ->where('status', 'pending')
            ->get();

        foreach ($regularizations as $regularization) {
            $regularization->update(['status' => $status, 'approved_by' => Auth::user()->employee->id]);

            if ($status == 'approved') {
                $this->updateAttendanceFromRegularization($regularization);
            }
        }

        return redirect()->route('regularization.requests.index')
            ->with('success', 'Selected requests have been ' . $status . '.');
    }

    private function updateAttendanceFromRegularization(AttendanceRegularization $regularization)
    {
        $approver = Auth::user()->employee;

        $attendance = Attendance::firstOrNew(
            [
                'employee_id' => $regularization->employee_id,
                'date' => $regularization->date,
            ]
        );

        $attendance->status = 'Present';
        // if ($regularization->check_in) {
        //     $attendance->check_in = $regularization->date . ' ' . $regularization->check_in;
        // }
        // if ($regularization->check_out) {
        //     $attendance->check_out = $regularization->date . ' ' . $regularization->check_out;
        // }
        $attendance->approver_id = $approver->id;
        $attendance->approver_name = $approver->name;
        // $attendance->remarks = 'Regularized by manager.';
        
        $attendance->save();
    }
}
