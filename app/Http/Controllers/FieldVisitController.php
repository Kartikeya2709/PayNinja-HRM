<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\FieldVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class FieldVisitController extends Controller
{
    /**
     * Store a new field visit (employee creates it with all details).
     */
    public function store(Request $request)
    {
        $request->validate([
            'visit_title' => 'required|string|max:255',
            'visit_description' => 'nullable|string',
            'location_name' => 'required|string|max:255',
            'location_address' => 'required|string|max:255',
            'visit_notes' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'current_location' => 'nullable|array',
            'visit_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20048',
            'scheduled_start_datetime' => 'nullable|date',
            'scheduled_end_datetime' => 'nullable|date|after_or_equal:scheduled_start_datetime',
        ]);

        $employee = Auth::user()->employee;

        // Only check for overlapping visits if both start and end dates are provided
        // if ($request->filled('scheduled_start_datetime') && $request->filled('scheduled_end_datetime')) {
        //     $startDateTime = Carbon::parse($request->scheduled_start_datetime);
        //     $endDateTime = Carbon::parse($request->scheduled_end_datetime);
            
        //     $overlappingVisits = FieldVisit::where('employee_id', $employee->id)
        //         ->where(function ($query) use ($startDateTime, $endDateTime) {
        //             $query->where(function ($q) use ($startDateTime, $endDateTime) {
        //                 // Check for overlapping time ranges
        //                 $q->where('scheduled_start_datetime', '<', $endDateTime)
        //                   ->where('scheduled_end_datetime', '>', $startDateTime);
        //             })->whereIn('approval_status', ['approved'])
        //               ;
        //         })->exists();

        //     if ($overlappingVisits) {   
        //         return back()->withInput()->withErrors(['scheduled_start_datetime' => 'You already have an approved/active field visit during this time period. Please choose a different time slot.']);
        //     }
        // }

        // Handle photo uploads
        $photoPaths = [];
        if ($request->hasFile('visit_photos')) {
            foreach ($request->file('visit_photos') as $photo) {
                $photoPaths[] = $photo->store('field-visit-photos', 'public');
            }
        }

        $fieldVisit = FieldVisit::create([
            'employee_id' => $employee->id,
            'reporting_manager_id' => $employee->reporting_manager_id,
            'visit_title' => $request->visit_title,
            'visit_description' => $request->visit_description,
            'location_name' => $request->location_name,
            'location_address' => $request->location_address,
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
            'current_location' =>$request->current_location ?? null,
            'visit_notes' => $request->visit_notes ?? 'N/A',
            'visit_attachments' => $photoPaths,
            'scheduled_start_datetime' => $request->scheduled_start_datetime ?? null,
            'scheduled_end_datetime' => $request->scheduled_end_datetime ?? null,
            'status' => 'scheduled'
        ]);

        return redirect()->to('/field-visits')->with('success', 'Field visit request submitted with details and sent for approval.');
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
     * Display a listing of field visits based on user role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Build base query based on user role with company filtering
        $query = FieldVisit::with(['employee', 'reportingManager'])
            ->whereHas('employee', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });

        if ($user->hasRole(['admin', 'company_admin'])) {
            // Admins see all visits within their company
        } elseif ($user->hasRole(['manager'])) {
            // Managers see visits within their company where they are reporting manager or the employee
            $query->where(function ($q) use ($user) {
                $q->where('reporting_manager_id', $user->employee->id)
                  ->orWhere('employee_id', $user->employee->id);
            });
        } else {
            // Regular employees see only their own visits
            $query->where('employee_id', $user->employee->id);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        if ($request->filled('date_from')) {
            $query->where('scheduled_start_datetime', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('scheduled_start_datetime', '<=', $request->date_to . ' 23:59:59');
        }

        // Order by scheduled start datetime (newest first)
        $query->orderBy('scheduled_start_datetime', 'desc');

        // Paginate results
        $fieldVisits = $query->paginate(15)->withQueryString();

        return view('field_visits.index', compact('fieldVisits'));
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
            'visit_notes' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'current_location' => 'nullable|array',
            'visit_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20048',
            'scheduled_start_datetime' => 'nullable|date',
            'scheduled_end_datetime' => 'nullable|date|after:scheduled_start_datetime',
        ]);

        // Handle photo uploads
        $photoPaths = $fieldVisit->visit_attachments ?? [];
        if ($request->hasFile('visit_photos')) {
            foreach ($request->file('visit_photos') as $photo) {
                $photoPaths[] = $photo->store('field-visit-photos', 'public');
            }
        }

        $fieldVisit->update([
            'visit_title' => $request->visit_title,
            'visit_description' => $request->visit_description,
            'location_name' => $request->location_name,
            'location_address' => $request->location_address,
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
            'current_location' => $request->current_location ?? 'N/A',
            'visit_notes' => $request->visit_notes ?? 'N/A',
            'visit_attachments' => $photoPaths,
            'scheduled_start_datetime' => $request->scheduled_start_datetime ?? null,
            'scheduled_end_datetime' => $request->scheduled_end_datetime ?? null,
        ]);

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
