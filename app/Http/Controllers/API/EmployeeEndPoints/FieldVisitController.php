<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\Controller;
use App\Models\FieldVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;

class FieldVisitController extends Controller
{
    /**
     * Get employee's field visits
     */
    public function getFieldVisits(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'status' => 'nullable|in:scheduled,in_progress,completed,cancelled',
                'approval_status' => 'nullable|in:pending,approved,rejected',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'search' => 'nullable|string|max:100',
                'location' => 'nullable|string|max:100',
                'sort_by' => 'nullable|in:scheduled_start_datetime,scheduled_end_datetime,created_at,status,approval_status,visit_title',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
                'upcoming_only' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json(['message' => 'Employee record not found'], 404);
            }

            $query = $employee->fieldVisits()
                ->with(['reportingManager']);

            // Status filter
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Approval status filter
            if ($request->approval_status) {
                $query->where('approval_status', $request->approval_status);
            }

            // Date range filter
            if ($request->start_date && $request->end_date) {
                $query->whereBetween('scheduled_start_datetime', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            }

            // Search in title and description
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('visit_title', 'like', '%' . $request->search . '%')
                        ->orWhere('visit_description', 'like', '%' . $request->search . '%')
                        ->orWhere('visit_notes', 'like', '%' . $request->search . '%');
                });
            }

            // Location search
            if ($request->location) {
                $query->where(function ($q) use ($request) {
                    $q->where('location_name', 'like', '%' . $request->location . '%')
                        ->orWhere('location_address', 'like', '%' . $request->location . '%');
                });
            }

            // Upcoming visits filter
            if ($request->upcoming_only) {
                $query->where('scheduled_start_datetime', '>', now())
                    ->where('status', '!=', 'cancelled');
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'scheduled_start_datetime');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $fieldVisits = $query->paginate($perPage);

            // Transform the data
            $fieldVisits->getCollection()->transform(function ($visit) {
                return [
                    'id' => $visit->id,
                    'visit_title' => $visit->visit_title,
                    'visit_description' => $visit->visit_description,
                    'location_name' => $visit->location_name,
                    'location_address' => $visit->location_address,
                    'latitude' => $visit->latitude,
                    'longitude' => $visit->longitude,
                    'scheduled_start_datetime' => $visit->scheduled_start_datetime,
                    'scheduled_end_datetime' => $visit->scheduled_end_datetime,
                    'actual_start_datetime' => $visit->actual_start_datetime,
                    'actual_end_datetime' => $visit->actual_end_datetime,
                    'status' => $visit->status,
                    'approval_status' => $visit->approval_status,
                    'visit_notes' => $visit->visit_notes,
                    'manager_feedback' => $visit->manager_feedback,
                    'visit_attachments' => $visit->visit_attachments,
                    'approved_at' => $visit->approved_at,
                    'reporting_manager' => $visit->reportingManager ? [
                        'id' => $visit->reportingManager->id,
                        'name' => $visit->reportingManager->name,
                        'employee_code' => $visit->reportingManager->employee_code,
                    ] : null,
                    'created_at' => $visit->created_at,
                    'updated_at' => $visit->updated_at,
                ];
            });

            // Get statistics for summary
            $summary = [
                'total_visits' => $query->count(),
                'status_summary' => [
                    'scheduled' => $query->where('status', 'scheduled')->count(),
                    'in_progress' => $query->where('status', 'in_progress')->count(),
                    'completed' => $query->where('status', 'completed')->count(),
                    'cancelled' => $query->where('status', 'cancelled')->count()
                ],
                'approval_summary' => [
                    'pending' => $query->where('approval_status', 'pending')->count(),
                    'approved' => $query->where('approval_status', 'approved')->count(),
                    'rejected' => $query->where('approval_status', 'rejected')->count()
                ],
            ];

            return response()->json([
                'field_visits' => $fieldVisits->items(),
                'pagination' => [
                    'current_page' => $fieldVisits->currentPage(),
                    'last_page' => $fieldVisits->lastPage(),
                    'per_page' => $fieldVisits->perPage(),
                    'total' => $fieldVisits->total()
                ],
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving field visits',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific field visit details
     */
    public function getFieldVisit($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $visit = $employee->fieldVisits()
            ->with(['reportingManager', 'approvedBy'])
            ->findOrFail($id);

        return response()->json([
            'field_visit' => [
                'id' => $visit->id,
                'visit_title' => $visit->visit_title,
                'visit_description' => $visit->visit_description,
                'location_name' => $visit->location_name,
                'location_address' => $visit->location_address,
                'latitude' => $visit->latitude,
                'longitude' => $visit->longitude,
                'scheduled_start_datetime' => $visit->scheduled_start_datetime,
                'scheduled_end_datetime' => $visit->scheduled_end_datetime,
                'actual_start_datetime' => $visit->actual_start_datetime,
                'actual_end_datetime' => $visit->actual_end_datetime,
                'status' => $visit->status,
                'approval_status' => $visit->approval_status,
                'visit_notes' => $visit->visit_notes,
                'manager_feedback' => $visit->manager_feedback,
                'visit_attachments' => $visit->visit_attachments,
                'approved_at' => $visit->approved_at,
                'reporting_manager' => $visit->reportingManager ? [
                    'id' => $visit->reportingManager->id,
                    'name' => $visit->reportingManager->name,
                    'employee_code' => $visit->reportingManager->employee_code,
                ] : null,
                'approved_by' => $visit->approvedBy ? [
                    'id' => $visit->approvedBy->id,
                    'name' => $visit->approvedBy->name,
                    'employee_code' => $visit->approvedBy->employee_code,
                ] : null,
                'created_at' => $visit->created_at,
                'updated_at' => $visit->updated_at,
            ]
        ]);
    }

    /**
     * Get upcoming field visits
     */
    public function getUpcomingFieldVisits()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $upcomingVisits = $employee->fieldVisits()
            ->where('scheduled_start_datetime', '>', now())
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_start_datetime', 'asc')
            ->get()
            ->map(function ($visit) {
                return [
                    'id' => $visit->id,
                    'visit_title' => $visit->visit_title,
                    'visit_description' => $visit->visit_description,
                    'location_name' => $visit->location_name,
                    'location_address' => $visit->location_address,
                    'scheduled_start_datetime' => $visit->scheduled_start_datetime,
                    'scheduled_end_datetime' => $visit->scheduled_end_datetime,
                    'status' => $visit->status,
                    'approval_status' => $visit->approval_status,
                ];
            });

        return response()->json([
            'upcoming_field_visits' => $upcomingVisits
        ]);
    }

    /**
     * Get field visit statistics
     */
    public function getFieldVisitStats()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $stats = [
            'total_visits' => $employee->fieldVisits()->count(),
            'completed_visits' => $employee->fieldVisits()->where('status', 'completed')->count(),
            'pending_visits' => $employee->fieldVisits()->where('status', 'scheduled')->count(),
            'in_progress_visits' => $employee->fieldVisits()->where('status', 'in_progress')->count(),
            'approved_visits' => $employee->fieldVisits()->where('approval_status', 'approved')->count(),
            'pending_approval_visits' => $employee->fieldVisits()->where('approval_status', 'pending')->count(),
        ];

        return response()->json([
            'field_visit_stats' => $stats
        ]);
    }

    public function CreateFieldVisit()
    {

        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $validator = validator(request()->all(), [
            'visit_title' => 'required|string|max:255',
            'visit_description' => 'nullable|string',
            'location_name' => 'required|string|max:255',
            'location_address' => 'required|string|max:500',
            'scheduled_start_datetime' => 'required|date|after:now',
            'scheduled_end_datetime' => 'required|date|after:scheduled_start_datetime',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $fieldVisit = FieldVisit::create([
            'employee_id' => $employee->id,
            'visit_title' => $validated['visit_title'],
            'visit_description' => $validated['visit_description'] ?? null,
            'reporting_manager_id' => $employee->reporting_manager_id,
            'location_name' => $validated['location_name'],
            'location_address' => $validated['location_address'],
            'scheduled_start_datetime' => $validated['scheduled_start_datetime'],
            'scheduled_end_datetime' => $validated['scheduled_end_datetime'],
            'status' => 'scheduled',
            'approval_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Field visit created successfully',
            'field_visit' => $fieldVisit
        ], 201);


    }

    public function StartFieldVisit()
    {
        $user = Auth::user();
        $employee = $user->employee;

        $fieldVisit = FieldVisit::where('employee_id', $employee->id)
            ->where('id', request()->id)
            ->where('status', 'scheduled')
            ->first();

        if (!$fieldVisit) {
            return response()->json(['message' => 'Field visit not found or cannot be started'], 404);
        }

        $fieldVisit->update([
            'actual_start_datetime' => now(),
            'status' => 'in_progress',
        ]);

        return response()->json([
            'message' => 'Field visit started successfully',
            'field_visit' => $fieldVisit
        ]);
    }

    public function CompleteFieldVisit()
    {
        $user = Auth::user();
        $employee = $user->employee;

        $fieldVisit = FieldVisit::where('employee_id', $employee->id)
            ->where('id', request()->id)
            ->where('status', 'in_progress')
            ->first();

        $validator = validator(request()->all(), [
            'visit_notes' => 'nullable|string',
            'manager_feedback' => 'nullable|string',
            'visit_attachments' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'visit_attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        if (!$fieldVisit) {
            return response()->json(['message' => 'Field visit not found or not in progress'], 404);
        }

        $fieldVisit->update([
            'actual_end_datetime' => now(),
            'status' => 'completed',
            'visit_notes' => $validated['visit_notes'] ?? $fieldVisit->visit_notes,
            'manager_feedback' => $validated['manager_feedback'] ?? $fieldVisit->manager_feedback,
            'latitude' => $validated['latitude'] ?? $fieldVisit->latitude,
            'longitude' => $validated['longitude'] ?? $fieldVisit->longitude,
        ]);

        return response()->json([
            'message' => 'Field visit completed successfully',
            'field_visit' => $fieldVisit
        ]);
    }

}
