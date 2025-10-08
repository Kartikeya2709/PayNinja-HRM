<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\API\BaseApiController;
use App\Models\AttendanceRegularization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceRegularizationController extends BaseApiController
{
    /**
     * Get all regularization requests for the authenticated employee
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegularizationRequests(Request $request)
    {
        try {
            $employee = Auth::user()->employee;
            $query = AttendanceRegularization::where('employee_id', $employee->id)
                ->with(['approver:id,name', 'employee:id,name,employee_code']);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('from_date') && $request->has('to_date')) {
                $query->whereBetween('date', [
                    Carbon::parse($request->from_date)->startOfDay(),
                    Carbon::parse($request->to_date)->endOfDay()
                ]);
            }

            // Filter by month and year
            if ($request->has('month') && $request->has('year')) {
                $query->whereMonth('date', $request->month)
                    ->whereYear('date', $request->year);
            }

            // Get the paginated results
            $perPage = $request->get('per_page', 10);
            $regularizations = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Transform the data
            $transformedData = $regularizations->map(function ($request) {
                return [
                    'id' => $request->id,
                    'date' => Carbon::parse($request->date)->format('Y-m-d'),
                    'type' => $request->type,
                    'reason' => $request->reason,
                    'status' => $request->status,
                    'requested_check_in' => $request->requested_check_in ? 
                        Carbon::parse($request->requested_check_in)->format('H:i:s') : null,
                    'requested_check_out' => $request->requested_check_out ? 
                        Carbon::parse($request->requested_check_out)->format('H:i:s') : null,
                    'approver' => $request->approver ? [
                        'id' => $request->approver->id,
                        'name' => $request->approver->name
                    ] : null,
                    'approved_at' => $request->approved_at ? 
                        Carbon::parse($request->approved_at)->format('Y-m-d H:i:s') : null,
                    'rejected_at' => $request->rejected_at ? 
                        Carbon::parse($request->rejected_at)->format('Y-m-d H:i:s') : null,
                    'rejection_reason' => $request->rejection_reason,
                    'created_at' => Carbon::parse($request->created_at)->format('Y-m-d H:i:s')
                ];
            });

            // Prepare the response with pagination information
            $response = [
                'current_page' => $regularizations->currentPage(),
                'last_page' => $regularizations->lastPage(),
                'per_page' => $regularizations->perPage(),
                'total' => $regularizations->total(),
                'requests' => $transformedData
            ];

            // Get summary counts
            $summaryCounts = [
                'total' => $employee->attendanceRegularizations()->count(),
                'pending' => $employee->attendanceRegularizations()->where('status', 'pending')->count(),
                'approved' => $employee->attendanceRegularizations()->where('status', 'approved')->count(),
                'rejected' => $employee->attendanceRegularizations()->where('status', 'rejected')->count()
            ];

            return $this->sendResponse([
                'summary' => $summaryCounts,
                'regularizations' => $response
            ], 'Regularization requests retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Error retrieving regularization requests', [$e->getMessage()], 500);
        }
    }

    public function CreateRegularizationRequest(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'date' => 'required|date|before_or_equal:today',
                'check_in' => 'nullable|date_format:H:i',
                'check_out' => 'nullable|date_format:H:i',
                'reason' => 'required|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
            }

            $employee = Auth::user()->employee;
            $batchId = \Str::uuid();

            $validated = $validator->validated();
            
            $regularization = $employee->attendanceRegularizations()->create([
                'request_batch_id' => $batchId,
                'reporting_manager_id' => $employee->reporting_manager_id,
                'date' => $validated['date'],
                'check_in' => $validated['check_in'] ?? null,
                'check_out' => $validated['check_out'] ?? null,
                'reason' => $validated['reason'],
                'status' => 'pending',
            ]);
        
            return $this->sendResponse(
                ['regularization' => $regularization],
                'Regularization request submitted successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error submitting regularization request', [$e->getMessage()], 500);
        }
    }

    public function UpdateRegularizationRequest(Request $request, $id)
    {
        try {
            $validator = validator($request->all(), [
                'check_in' => 'nullable|date_format:H:i',
                'check_out' => 'nullable|date_format:H:i',
                'reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors()->toArray(), 422);
            }

            $employee = Auth::user()->employee;
            $regularization = $employee->attendanceRegularizations()->where('id', $id)->first();

            if (!$regularization) {
                return $this->sendError('Regularization request not found', [], 404);
            }

            if ($regularization->status !== 'pending') {
                return $this->sendError('Only pending requests can be updated', [], 400);
            }

            $regularization->update($validator->validated());

            return $this->sendResponse(
                ['regularization' => $regularization],
                'Regularization request updated successfully'
            );
        } catch (\Exception $e) {
            return $this->sendError('Error updating regularization request', [$e->getMessage()], 500);
        }
    }
}
