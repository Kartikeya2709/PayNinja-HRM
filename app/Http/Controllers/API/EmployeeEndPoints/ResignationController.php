<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\Controller;
use App\Models\EmployeeResignation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Log;

class ResignationController extends Controller
{
    /**
     * Get employee's resignations
     */
    public function getResignations(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'status' => 'nullable|in:pending,hr_approved,manager_approved,approved,rejected,withdrawn',
                'resignation_type' => 'nullable|in:voluntary,involuntary,retirement,contract_end',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
                'search' => 'nullable|string|max:100',
                'exit_process' => 'nullable|in:pending,completed',
                'sort_by' => 'nullable|in:created_at,resignation_date,last_working_date,status',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100',
                'active_only' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json(['message' => 'Employee record not found'], 404);
            }

            $query = $employee->resignations()
                ->with(['reportingManager', 'hrAdmin', 'approver']);

            // Status filter
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Resignation type filter
            if ($request->resignation_type) {
                $query->where('resignation_type', $request->resignation_type);
            }

            // Date range filter
            if ($request->from_date && $request->to_date) {
                $query->whereBetween('resignation_date', [
                    $request->from_date,
                    $request->to_date
                ]);
            }

            // Search in reason and remarks
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('reason', 'like', '%' . $request->search . '%')
                      ->orWhere('employee_remarks', 'like', '%' . $request->search . '%');
                });
            }

            // Exit process filter
            if ($request->exit_process === 'completed') {
                $query->where('exit_interview_completed', true)
                      ->where('handover_completed', true)
                      ->where('assets_returned', true)
                      ->where('final_settlement_completed', true);
            } elseif ($request->exit_process === 'pending') {
                $query->where(function($q) {
                    $q->where('exit_interview_completed', false)
                      ->orWhere('handover_completed', false)
                      ->orWhere('assets_returned', false)
                      ->orWhere('final_settlement_completed', false);
                });
            }

            // Active resignations filter
            if ($request->active_only) {
                $query->where(function($q) {
                    $q->whereIn('status', ['pending', 'hr_approved'])
                      ->where('resignation_date', '>=', now());
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $resignations = $query->paginate($perPage);

            // Transform the data
            $resignations->getCollection()->transform(function ($resignation) {
                return [
                    'id' => $resignation->id,
                    'resignation_type' => $resignation->resignation_type,
                    'reason' => $resignation->reason,
                    'resignation_date' => $resignation->resignation_date,
                    'last_working_date' => $resignation->last_working_date,
                    'notice_period_days' => $resignation->notice_period_days,
                    'status' => $resignation->status,
                    'employee_remarks' => $resignation->employee_remarks,
                    'manager_remarks' => $resignation->manager_remarks,
                    'hr_remarks' => $resignation->hr_remarks,
                    'admin_remarks' => $resignation->admin_remarks,
                    'exit_interview_completed' => $resignation->exit_interview_completed,
                    'exit_interview_date' => $resignation->exit_interview_date,
                    'handover_completed' => $resignation->handover_completed,
                    'assets_returned' => $resignation->assets_returned,
                    'final_settlement_completed' => $resignation->final_settlement_completed,
                    'approved_at' => $resignation->approved_at,
                    'remaining_days' => $resignation->remainingdays,
                    'is_active' => $resignation->isActive(),
                    'can_be_withdrawn' => $resignation->canBeWithdrawn(),
                    'requires_exit_process' => $resignation->requiresExitProcess(),
                    'is_exit_process_complete' => $resignation->isExitProcessComplete(),
                    'reporting_manager' => $resignation->reportingManager ? [
                        'id' => $resignation->reportingManager->id,
                        'name' => $resignation->reportingManager->name,
                        'employee_code' => $resignation->reportingManager->employee_code,
                    ] : null,
                    'created_at' => $resignation->created_at,
                    'updated_at' => $resignation->updated_at,
                ];
            });

            // Calculate summary statistics
            $summary = [
                'total_resignations' => $query->count(),
                'status_summary' => [
                    'pending' => $query->where('status', 'pending')->count(),
                    'hr_approved' => $query->where('status', 'hr_approved')->count(),
                    'approved' => $query->where('status', 'approved')->count(),
                    'rejected' => $query->where('status', 'rejected')->count(),
                    'withdrawn' => $query->where('status', 'withdrawn')->count()
                ],
                'exit_process_summary' => [
                    'completed' => $query->where('exit_interview_completed', true)
                                       ->where('handover_completed', true)
                                       ->where('assets_returned', true)
                                       ->where('final_settlement_completed', true)
                                       ->count(),
                    'pending' => $query->where(function($q) {
                        $q->where('exit_interview_completed', false)
                          ->orWhere('handover_completed', false)
                          ->orWhere('assets_returned', false)
                          ->orWhere('final_settlement_completed', false);
                    })->count()
                ]
            ];

            return response()->json([
                'resignations' => $resignations->items(),
                'pagination' => [
                    'current_page' => $resignations->currentPage(),
                    'last_page' => $resignations->lastPage(),
                    'per_page' => $resignations->perPage(),
                    'total' => $resignations->total()
                ],
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving resignations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific resignation details
     */
    public function getResignation($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $resignation = $employee->resignations()
            ->with(['reportingManager', 'hrAdmin', 'approver'])
            ->findOrFail($id);

        return response()->json([
            'resignation' => [
                'id' => $resignation->id,
                'resignation_type' => $resignation->resignation_type,
                'reason' => $resignation->reason,
                'resignation_date' => $resignation->resignation_date,
                'last_working_date' => $resignation->last_working_date,
                'notice_period_days' => $resignation->notice_period_days,
                'attachment_path' => $resignation->attachment_path,
                'status' => $resignation->status,
                'employee_remarks' => $resignation->employee_remarks,
                'manager_remarks' => $resignation->manager_remarks,
                'hr_remarks' => $resignation->hr_remarks,
                'admin_remarks' => $resignation->admin_remarks,
                'exit_interview_completed' => $resignation->exit_interview_completed,
                'exit_interview_date' => $resignation->exit_interview_date,
                'handover_completed' => $resignation->handover_completed,
                'handover_document_path' => $resignation->handover_document_path,
                'assets_returned' => $resignation->assets_returned,
                'final_settlement_completed' => $resignation->final_settlement_completed,
                'approved_at' => $resignation->approved_at,
                'remaining_days' => $resignation->remaining_days,
                'is_active' => $resignation->isActive(),
                'can_be_withdrawn' => $resignation->canBeWithdrawn(),
                'requires_exit_process' => $resignation->requiresExitProcess(),
                'is_exit_process_complete' => $resignation->isExitProcessComplete(),
                'reporting_manager' => $resignation->reportingManager ? [
                    'id' => $resignation->reportingManager->id,
                    'name' => $resignation->reportingManager->name,
                    'employee_code' => $resignation->reportingManager->employee_code,
                ] : null,
                'created_at' => $resignation->created_at,
                'updated_at' => $resignation->updated_at,
            ]
        ]);
    }

    /**
     * Create a new resignation request
     */
    public function createResignation(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        // Log::info($employee);

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        // Check if employee already has an active resignation
        if ($employee->hasActiveResignation()) {
            return response()->json(['message' => 'You already have an active resignation request'], 400);
        }
        $validator = validator($request->all(), [
            'resignation_type' => 'required|in:voluntary,involuntary,retirement,contract_end',
            'reason' => 'required|string|max:1000',
            'resignation_date' => 'required|date|after:today',
            'last_working_date' => 'required|date|after:resignation_date',
            'notice_period_days' => 'nullable|integer|min:0',
            'employee_remarks' => 'nullable|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        
        // Log::info($validated);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('resignations', 'public');
        }

        $resignation = EmployeeResignation::create([
            'employee_id' => $employee->id,
            'company_id' => $employee->company_id,
            'resignation_type' => $validated['resignation_type'],
            'reason' => $validated['reason'],
            'resignation_date' => $validated['resignation_date'],
            'last_working_date' => $validated['last_working_date'],
            'notice_period_days' => $validated['notice_period_days'] ?? null,
            'employee_remarks' => $validated['employee_remarks'] ?? null,
            'attachment_path' => $attachmentPath,
            'status' => 'pending',
            'reporting_manager_id' => $employee->reporting_manager_id,
        ]);  

        return response()->json([
            'message' => 'Resignation request submitted successfully',
            'resignation' => [
                'id' => $resignation->id,
                'resignation_type' => $resignation->resignation_type,
                'reason' => $resignation->reason,
                'resignation_date' => $resignation->resignation_date,
                'last_working_date' => $resignation->last_working_date,
                'status' => $resignation->status,
                'created_at' => $resignation->created_at,
            ]
        ], 201);
    }

    /**
     * Update resignation request (only if pending)
     */
    public function updateResignation(Request $request, $id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $resignation = $employee->resignations()
            ->whereIn('status', ['pending', 'hr_approved'])
            ->findOrFail($id);

        $validator = validator($request->all(), [
            'reason' => 'sometimes|required|string|max:1000',
            'resignation_date' => 'sometimes|required|date|after:today',
            'last_working_date' => 'sometimes|required|date|after:resignation_date',
            'notice_period_days' => 'nullable|integer|min:0',
            'employee_remarks' => 'sometimes|required|string|max:1000',
            'attachment' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        $updateData = [];

        if (isset($validated['reason'])) {
            $updateData['reason'] = $validated['reason'];
        }
        if (isset($validated['resignation_date'])) {
            $updateData['resignation_date'] = $validated['resignation_date'];
        }
        if (isset($validated['last_working_date'])) {
            $updateData['last_working_date'] = $validated['last_working_date'];
        }
        if (isset($validated['notice_period_days'])) {
            $updateData['notice_period_days'] = $validated['notice_period_days'];
        }
        if (isset($validated['employee_remarks'])) {
            $updateData['employee_remarks'] = $validated['employee_remarks'];
        }

        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($resignation->attachment_path) {
                Storage::disk('public')->delete($resignation->attachment_path);
            }
            $updateData['attachment_path'] = $request->file('attachment')->store('resignations', 'public');
        }

        $resignation->update($updateData);

        return response()->json([
            'message' => 'Resignation request updated successfully',
            'resignation' => [
                'id' => $resignation->id,
                'reason' => $resignation->reason,
                'resignation_date' => $resignation->resignation_date,
                'last_working_date' => $resignation->last_working_date,
                'status' => $resignation->status,
                'updated_at' => $resignation->updated_at,
            ]
        ]);
    }

    /**
     * Withdraw resignation request (only if can be withdrawn)
     */
    public function withdrawResignation($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $resignation = $employee->resignations()
            ->whereIn('status', ['pending', 'hr_approved'])
            ->findOrFail($id);

        if (!$resignation->canBeWithdrawn()) {
            return response()->json(['message' => 'This resignation cannot be withdrawn at this stage'], 400);
        }

        // Delete attachment if exists
        if ($resignation->attachment_path) { 
            Storage::disk('public')->delete($resignation->attachment_path);
        }

        $resignation->update(['status' => 'withdrawn']);

        return response()->json([
            'message' => 'Resignation request withdrawn successfully'
        ]);
    }

    /**
     * Get resignation types
     */
    public function getResignationTypes()
    {
        $types = [
            'voluntary' => 'Voluntary Resignation',
            'involuntary' => 'Involuntary Termination',
            'retirement' => 'Retirement',
            'contract_end' => 'Contract End',
        ];

        return response()->json([
            'resignation_types' => $types
        ]);
    }
}
