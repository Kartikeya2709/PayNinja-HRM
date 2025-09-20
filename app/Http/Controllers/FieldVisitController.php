<?php

namespace App\Http\Controllers;

use App\Models\FieldVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class FieldVisitController extends Controller
{
    /**
     * Store a new field visit (employee creates it).
     */
    public function store(Request $request)
    {
        $request->validate([
            'visit_title' => 'required|string|max:255',
            'visit_description' => 'nullable|string',
            'location_name' => 'required|string|max:255',
            'location_address' => 'required|string|max:255',
            'scheduled_start_datetime' => 'required|date',
            'scheduled_end_datetime' => 'required|date|after:scheduled_start_datetime',
        ]);

        $employee = Auth::user()->employee;

        $fieldVisit = FieldVisit::create([
            'employee_id' => $employee->id,
            'reporting_manager_id' => $employee->reporting_manager_id,
            'visit_title' => $request->visit_title,
            'visit_description' => $request->visit_description,
            'location_name' => $request->location_name,
            'location_address' => $request->location_address,
            'scheduled_start_datetime' => $request->scheduled_start_datetime,
            'scheduled_end_datetime' => $request->scheduled_end_datetime,
        ]);

        return redirect()->to('/field-visits')->with('success', 'Field visit created and sent for approval.');
    }

    /**
     * Reporting Manager approves visit.
     */
    public function approve(FieldVisit $fieldVisit)
    {
        $manager = Auth::user()->employee;

        if ($manager->id !== $fieldVisit->reporting_manager_id && !Auth::user()->hasRole(['admin', 'company_admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $fieldVisit->approve($manager);

        return redirect()->back()->with('success', 'Field visit approved.');
    }

    /**
     * Reporting Manager rejects visit.
     */
    public function reject(FieldVisit $fieldVisit)
    {
        $manager = Auth::user()->employee;

        if ($manager->id !== $fieldVisit->reporting_manager_id && !Auth::user()->hasRole(['admin', 'company_admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $fieldVisit->reject($manager);

        return redirect()->back()->with('success', 'Field visit rejected.');
    }

    /**
     * Employee starts visit after approval.
     */
    public function start(FieldVisit $fieldVisit)
    {
        $employee = Auth::user()->employee;

        if ($employee->id !== $fieldVisit->employee_id || !$fieldVisit->isApproved()) {
            abort(403, 'Unauthorized action.');
        }

        $fieldVisit->startVisit();

        return redirect()->back()->with('success', 'Field visit started.');
    }

    /**
     * Employee completes visit.
     */
    public function complete(Request $request, FieldVisit $fieldVisit)
    {
        $employee = Auth::user()->employee;

        if ($employee->id !== $fieldVisit->employee_id || !$fieldVisit->isInProgress()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'visit_notes' => 'required|string',
            'visit_photos' => 'nullable|array',
            'visit_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:20048',
        ]);

        $photoPaths = [];
        if ($request->hasFile('visit_photos')) {
            foreach ($request->file('visit_photos') as $photo) {
                $path = $photo->store('field-visit-photos', 'public');
                $photoPaths[] = $path;
            }
        }

        $fieldVisit->completeVisit([
            'visit_notes' => $request->visit_notes,
            'visit_attachments' => $photoPaths,
        ]);

        return redirect()->back()->with('success', 'Field visit completed.');
    }

    /**
     * Display a listing of field visits based on user role.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole(['admin', 'company_admin'])) {
            $visits = FieldVisit::with(['employee', 'reportingManager'])->get();
        } elseif ($user->hasRole(['manager'])) {
            $visits = FieldVisit::where('reporting_manager_id', $user->employee->id)
                ->orWhere('employee_id', $user->employee->id)
                ->with(['employee', 'reportingManager'])
                ->get();
        } else {
            $visits = FieldVisit::where('employee_id', $user->employee->id)
                ->with(['employee', 'reportingManager'])
                ->get();
        }

        return view('field_visits.index', ['fieldVisits' => $visits]);
    }

    /**
     * Show the form for creating a new field visit.
     */
    public function create()
    {
        return view('field_visits.create');
    }

    /**
     * Display the specified field visit.
     */
    public function show(FieldVisit $fieldVisit)
    {
        $user = Auth::user();
        if ($user->hasRole(['admin', 'company_admin']) ||
            $fieldVisit->employee_id === $user->employee->id ||
            $fieldVisit->reporting_manager_id === $user->employee->id) {
            return view('field_visits.show', compact('fieldVisit'));
        }
        abort(403);
    }

    /**
     * Show the form for editing the specified field visit.
     */
    public function edit(FieldVisit $fieldVisit)
    {
        $user = Auth::user();
        if (($fieldVisit->employee_id === $user->employee->id && $fieldVisit->isPendingApproval()) ||
            $user->hasRole(['admin', 'company_admin'])) {
            return view('field_visits.edit', compact('fieldVisit'));
        }
        abort(403);
    }

    /**
     * Update the specified field visit in storage.
     */
    public function update(Request $request, FieldVisit $fieldVisit)
    {
        $user = Auth::user();
        if (! (($fieldVisit->employee_id === $user->employee->id && $fieldVisit->isPendingApproval()) ||
            $user->hasRole(['admin', 'company_admin']))) {
            abort(403);
        }

        $request->validate([
            'visit_title' => 'required|string|max:255',
            'visit_description' => 'nullable|string',
            'location_name' => 'required|string|max:255',
            'location_address' => 'required|string|max:255',
            'scheduled_start_datetime' => 'required|date',
            'scheduled_end_datetime' => 'required|date|after:scheduled_start_datetime',
        ]);

        $fieldVisit->update($request->only([
            'visit_title', 'visit_description', 'location_name', 'location_address',
            'scheduled_start_datetime', 'scheduled_end_datetime'
        ]));

        return redirect()->route('field-visits.show', $fieldVisit)->with('success', 'Field visit updated successfully.');
    }

    /**
     * Remove the specified field visit from storage.
     */
    public function destroy(FieldVisit $fieldVisit)
    {
        $user = Auth::user();
        if (! (($fieldVisit->employee_id === $user->employee->id && $fieldVisit->isPendingApproval()) ||
            $user->hasRole(['admin', 'company_admin']))) {
            abort(403);
        }
        $fieldVisit->delete();
        return redirect()->route('field-visits.index')->with('success', 'Field visit deleted successfully.');
    }

    /**
     * Show pending approvals for reporting manager/admin.
     */
    public function pendingApprovals()
    {
        $user = Auth::user();

        if ($user->hasRole(['admin', 'company_admin'])) {
            $visits = FieldVisit::where('approval_status', 'pending')->with(['employee'])->get();
        } else {
            $visits = FieldVisit::where('reporting_manager_id', $user->employee->id)
                ->where('approval_status', 'pending')
                ->with(['employee'])
                ->get();
        }

        return view('field_visits.pending', compact('visits'));
    }
}
