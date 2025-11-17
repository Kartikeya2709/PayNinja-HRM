<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\Controller;
use App\Models\FieldVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\error;

class FieldVisitController extends Controller
{
    /**
     * Get employee's field visits
     */
    public function getFieldVisits(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'nullable|in:scheduled,approved,completed,cancelled',
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
                    'approved' => $query->where('status', 'approved')->count(),
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
            'approved_visits' => $employee->fieldVisits()->where('status', 'approved')->count(),
            'approved_visits_count' => $employee->fieldVisits()->where('approval_status', 'approved')->count(),
            'pending_approval_visits' => $employee->fieldVisits()->where('approval_status', 'pending')->count(),
        ];

        return response()->json([
            'field_visit_stats' => $stats
        ]);
    }

    /**
     * Get pending approvals for managers
     */
    public function getPendingApprovals()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $pendingVisits = FieldVisit::where('reporting_manager_id', $employee->id)
            ->where('approval_status', 'pending')
            ->with(['employee'])
            ->orderBy('scheduled_start_datetime', 'desc')
            ->get()
            ->map(function ($visit) {
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
                    'visit_notes' => $visit->visit_notes,
                    'visit_attachments' => $visit->visit_attachments,
                    'employee' => [
                        'id' => $visit->employee->id,
                        'name' => $visit->employee->name,
                        'employee_code' => $visit->employee->employee_code,
                    ],
                    'created_at' => $visit->created_at,
                ];
            });

        return response()->json([
            'pending_approvals' => $pendingVisits
        ]);
    }

    /**
     * Create a new field visit with all details
     */
    public function createFieldVisit(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'visit_title' => 'required|string|max:255',
            'visit_description' => 'nullable|string',
            'location_name' => 'required|string|max:255',
            'location_address' => 'required|string|max:500',
            'visit_notes' => 'string',
            'latitude' => 'numeric',
            'longitude' => 'numeric',
            'current_location' => 'nullable|string|max:500',
            'visit_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:20048',
            'scheduled_start_datetime' => 'required|date',
            'scheduled_end_datetime' => 'required|date|after_or_equal:scheduled_start_datetime',
        ]);

        if ($validator->fails()) {
            return response()->json( ['success'=>'false',
            'message' => $validator->errors()->first()], 422);
        }

        $validated = $validator->validated();

        // Edge Case 1: Check for overlapping approved/active visits
        // $startDateTime = Carbon::parse($validated['scheduled_start_datetime']);
        // $endDateTime = Carbon::parse($validated['scheduled_end_datetime']);
        
        // $overlappingVisits = FieldVisit::where('employee_id', $employee->id)
        //     ->where(function ($query) use ($startDateTime, $endDateTime) {
        //         // Check for time overlap
        //         $query->where('scheduled_start_datetime', '<', $endDateTime)
        //               ->where('scheduled_end_datetime', '>', $startDateTime)
        //               ->where(function ($q) {
        //                   // Check for approved visits OR visits with active statuses
        //                   $q->where('approval_status', 'approved')
        //                     ->orWhereIn('status', ['completed']);
        //               });
        //     })->exists();

        // if ($overlappingVisits) {
        //     return response()->json([
        //         'message' => 'You already have an approved field visit during this time period. Please choose a different time slot.',
        //         'error_code' => 'OVERLAPPING_VISIT_FOUND'
        //     ], 409);
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
            'visit_title' => $validated['visit_title'],
            'visit_description' => $validated['visit_description'] ?? null,
            'location_name' => $validated['location_name'],
            'location_address' => $validated['location_address'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'current_location' => $validated['current_location'] ?? 'N/A',
            'visit_notes' => $validated['visit_notes'] ?? 'N/A',
            'visit_attachments' => $photoPaths,
            'scheduled_start_datetime' => $validated['scheduled_start_datetime'],
            'scheduled_end_datetime' => $validated['scheduled_end_datetime'],
            'status' => 'scheduled',
            'approval_status' => 'pending',
        ]);

        return response()->json([
            'success'=>'true',
            'message' => 'Field visit request created successfully',
            'field_visit' => [
                'id' => $fieldVisit->id,
                'visit_title' => $fieldVisit->visit_title,
                'status' => $fieldVisit->status,
                'approval_status' => $fieldVisit->approval_status,
            ]
        ], 201);
    }

    /**
     * Approve a field visit
     */
    public function approveFieldVisit($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $fieldVisit = FieldVisit::findOrFail($id);

        // Check if user is the reporting manager
        if ($fieldVisit->reporting_manager_id !== $employee->id && !$this->hasAdminRole($user)) {
            return response()->json(['message' => 'Unauthorized to approve this field visit'], 403);
        }

        // Check if visit is still pending
        if ($fieldVisit->approval_status !== 'pending') {
            return response()->json(['message' => 'Field visit is not pending approval'], 400);
        }

        $fieldVisit->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $employee->id,
            'status' => 'approved'
        ]);

        return response()->json([
            'message' => 'Field visit approved successfully',
            'field_visit' => [
                'id' => $fieldVisit->id,
                'approval_status' => $fieldVisit->approval_status,
                'status' => $fieldVisit->status,
                'approved_at' => $fieldVisit->approved_at,
            ]
        ]);
    }

    /**
     * Reject a field visit
     */
    public function rejectFieldVisit(Request $request, $id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'manager_feedback' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $fieldVisit = FieldVisit::findOrFail($id);

        // Check if user is the reporting manager
        if ($fieldVisit->reporting_manager_id !== $employee->id && !$this->hasAdminRole($user)) {
            return response()->json(['message' => 'Unauthorized to reject this field visit'], 403);
        }

        // Check if visit is still pending
        if ($fieldVisit->approval_status !== 'pending') {
            return response()->json(['message' => 'Field visit is not pending approval'], 400);
        }

        $validated = $validator->validated();

        $fieldVisit->update([
            'approval_status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => $employee->id,
            'manager_feedback' => $validated['manager_feedback'],
        ]);

        return response()->json([
            'message' => 'Field visit rejected successfully',
            'field_visit' => [
                'id' => $fieldVisit->id,
                'approval_status' => $fieldVisit->approval_status,
                'approved_at' => $fieldVisit->approved_at,
                'manager_feedback' => $fieldVisit->manager_feedback,
            ]
        ]);
    }

    /**
     * Helper method to check if user has admin roles
     */
    private function hasAdminRole($user)
    {
        return $user->hasRole && method_exists($user, 'hasRole') ? $user->hasRole(['admin', 'company_admin']) : false;
    }
}
