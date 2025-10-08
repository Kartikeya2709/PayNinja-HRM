<?php

namespace App\Http\Controllers\API\EmployeeEndPoints;

use App\Http\Controllers\Controller;
use App\Models\Reimbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReimbursementController extends Controller
{
    /**
     * Get employee's reimbursements
     */
    public function getReimbursements(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'status' => 'nullable|in:pending,admin_approved,reporter_approved,rejected',
                'year' => 'nullable|integer|min:2000',
                'month' => 'nullable|integer|between:1,12',
                'from_date' => 'nullable|date',
                'to_date' => 'nullable|date|after_or_equal:from_date',
                'min_amount' => 'nullable|numeric|min:0',
                'max_amount' => 'nullable|numeric|min:0',
                'search' => 'nullable|string|max:100',
                'sort_by' => 'nullable|in:created_at,expense_date,amount,status,title',
                'sort_order' => 'nullable|in:asc,desc',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = Auth::user();
            $employee = $user->employee;

            if (!$employee) {
                return response()->json(['message' => 'Employee record not found'], 404);
            }

            $query = $employee->reimbursements();

            // Date range filter
            if ($request->from_date && $request->to_date) {
                $query->whereBetween('expense_date', [
                    $request->from_date,
                    $request->to_date
                ]);
            } else {
                // Year and month filter
                if ($request->year) {
                    $query->whereYear('expense_date', $request->year);
                }
                if ($request->month) {
                    $query->whereMonth('expense_date', $request->month);
                }
            }

            // Status filter
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Amount range filter
            if ($request->min_amount) {
                $query->where('amount', '>=', $request->min_amount);
            }
            if ($request->max_amount) {
                $query->where('amount', '<=', $request->max_amount);
            }

            // Search in title and description
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('title', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 10);
            $reimbursements = $query->paginate($perPage);

            // Transform the data
            $reimbursements->getCollection()->transform(function ($reimbursement) {
                return [
                    'id' => $reimbursement->id,
                    'title' => $reimbursement->title,
                    'description' => $reimbursement->description,
                    'amount' => $reimbursement->amount,
                    'expense_date' => $reimbursement->expense_date,
                    'receipt_path' => $reimbursement->receipt_path,
                    'status' => $reimbursement->status,
                    'reporter_remarks' => $reimbursement->reporter_remarks,
                    'admin_remarks' => $reimbursement->admin_remarks,
                    'remarks' => $reimbursement->remarks,
                    'created_at' => $reimbursement->created_at,
                    'updated_at' => $reimbursement->updated_at,
                ];
            });

            // Calculate summary statistics
            $summary = [
                'total_requests' => $query->count(),
                'total_amount' => $query->sum('amount'),
                'status_summary' => [
                    'pending' => $query->where('status', 'pending')->count(),
                    'approved' => $query->where('status', 'approved')->count(),
                    'rejected' => $query->where('status', 'rejected')->count(),
                    'cancelled' => $query->where('status', 'cancelled')->count()
                ]
            ];

            return response()->json([
                'reimbursements' => $reimbursements->items(),
                'pagination' => [
                    'current_page' => $reimbursements->currentPage(),
                    'last_page' => $reimbursements->lastPage(),
                    'per_page' => $reimbursements->perPage(),
                    'total' => $reimbursements->total()
                ],
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error retrieving reimbursements', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get specific reimbursement details
     */
    public function getReimbursement($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $reimbursement = $employee->reimbursements()
            ->findOrFail($id);

        return response()->json([
            'reimbursement' => [
                'id' => $reimbursement->id,
                'title' => $reimbursement->title,
                'description' => $reimbursement->description,
                'amount' => $reimbursement->amount,
                'expense_date' => $reimbursement->expense_date,
                'receipt_path' => $reimbursement->receipt_path,
                'status' => $reimbursement->status,
                'reporter_remarks' => $reimbursement->reporter_remarks,
                'admin_remarks' => $reimbursement->admin_remarks,
                'remarks' => $reimbursement->remarks,
                'created_at' => $reimbursement->created_at,
                'updated_at' => $reimbursement->updated_at,
            ]
        ]);
    }

    /**
     * Create a new reimbursement request
     */
    public function createReimbursement(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $validator = validator($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date|before_or_equal:today',
            'receipt' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('reimbursements', 'public');
        }

        $reimbursement = Reimbursement::create([
            'employee_id' => $employee->id,
            'company_id' => $employee->company_id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'expense_date' => $validated['expense_date'],
            'receipt_path' => $receiptPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Reimbursement request created successfully',
            'reimbursement' => [
                'id' => $reimbursement->id,
                'title' => $reimbursement->title,
                'description' => $reimbursement->description,
                'amount' => $reimbursement->amount,
                'expense_date' => $reimbursement->expense_date,
                'receipt_path' => $reimbursement->receipt_path,
                'status' => $reimbursement->status,
                'created_at' => $reimbursement->created_at,
            ]
        ], 201);
    }

    /**
     * Update reimbursement request (only if pending)
     */
    public function updateReimbursement(Request $request, $id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $reimbursement = $employee->reimbursements()
            ->where('status', 'pending')
            ->findOrFail($id);

        $validator = validator($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:1000',
            'amount' => 'sometimes|required|numeric|min:0',
            'expense_date' => 'sometimes|required|date|before_or_equal:today',
            'receipt' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $updateData = [];

        if (isset($validated['title'])) {
            $updateData['title'] = $validated['title'];
        }
        if (isset($validated['description'])) {
            $updateData['description'] = $validated['description'];
        }
        if (isset($validated['amount'])) {
            $updateData['amount'] = $validated['amount'];
        }
        if (isset($validated['expense_date'])) {
            $updateData['expense_date'] = $validated['expense_date'];
        }

        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($reimbursement->receipt_path) {
                Storage::disk('public')->delete($reimbursement->receipt_path);
            }
            $updateData['receipt_path'] = $request->file('receipt')->store('reimbursements', 'public');
        }

        $reimbursement->update($updateData);

        return response()->json([
            'message' => 'Reimbursement request updated successfully',
            'reimbursement' => [
                'id' => $reimbursement->id,
                'title' => $reimbursement->title,
                'description' => $reimbursement->description,
                'amount' => $reimbursement->amount,
                'expense_date' => $reimbursement->expense_date,
                'receipt_path' => $reimbursement->receipt_path,
                'status' => $reimbursement->status,
                'updated_at' => $reimbursement->updated_at,
            ]
        ]);
    }

    /**
     * Cancel reimbursement request (only if pending)
     */
    public function cancelReimbursement($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee record not found'], 404);
        }

        $reimbursement = $employee->reimbursements()
            ->where('status', 'pending')
            ->findOrFail($id);

        // Delete receipt file if exists
        if ($reimbursement->receipt_path) {
            Storage::disk('public')->delete($reimbursement->receipt_path);
        }

        $reimbursement->delete();

        return response()->json([
            'message' => 'Reimbursement request cancelled successfully'
        ]);
    }
}
