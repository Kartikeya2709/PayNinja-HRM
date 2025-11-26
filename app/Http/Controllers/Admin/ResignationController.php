<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeResignation;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ResignationController extends Controller
{
    /**
     * Display a listing of resignation requests for admin.
     */
    public function index(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $query = EmployeeResignation::whereHas('employee', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->with(['employee.department', 'employee.designation', 'reportingManager', 'approver']);

        // Apply filters
        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('resignation_type')) {
            $query->where('resignation_type', $request->resignation_type);
        }

        if ($request->filled('date_from')) {
            $query->where('resignation_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('resignation_date', '<=', $request->date_to);
        }

        $resignations = $query->latest()->paginate(15);
        $departments = Department::where('company_id', $companyId)->get();

        // Get summary statistics
        $stats = [
            'total' => EmployeeResignation::whereHas('employee', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->count(),
            'pending' => EmployeeResignation::whereHas('employee', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->where('status', 'pending')->count(),
            'approved' => EmployeeResignation::whereHas('employee', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->where('status', 'approved')->count(),
            'this_month' => EmployeeResignation::whereHas('employee', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })->whereMonth('resignation_date', now()->month)->count(),
        ];



        return view('admin.resignations.index', compact('resignations', 'departments', 'stats'));
    }

    /**
     * Display the specified resignation request.
     */
    public function show(EmployeeResignation $resignation)
    {
        // Check if resignation belongs to the admin's company
        if ($resignation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $resignation->load([
            'employee.department',
            'employee.designation',
            'reportingManager',
            'hrAdmin',
            'approver'
        ]);

        return view('admin.resignations.show', compact('resignation'));
    }

    /**
     * Approve the resignation request.
     */
    public function approve(Request $request, EmployeeResignation $resignation)
    {
        // Check if resignation belongs to the admin's company
        if ($resignation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if resignation can be approved
        if (!in_array($resignation->status, ['pending', 'hr_approved', 'manager_approved'])) {
            return redirect()->back()
                ->with('error', 'This resignation request cannot be approved at this stage.');
        }

        $user = Auth::user();
        $isHR = in_array($user->role, ['admin', 'company_admin']);

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
            'exit_interview_date' => 'nullable|date|after_or_equal:today|before_or_equal:' . $resignation->last_working_date,
        ]);

        // Determine next status based on current status and user role
        $newStatus = $this->determineNextApprovalStatus($resignation, $user);

        $updateData = [
            'status' => $newStatus,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ];

        if ($isHR) {
            $updateData['hr_admin_id'] = $user->id;
            if (!empty($validated['remarks'])) {
                $updateData['hr_remarks'] = $validated['remarks'];
            }
            if (!empty($validated['exit_interview_date'])) {
                $updateData['exit_interview_date'] = $validated['exit_interview_date'];
            }
        } else {
            if (!empty($validated['remarks'])) {
                $updateData['manager_remarks'] = $validated['remarks'];
            }
        }

        $resignation->update($updateData);

        // TODO: Send notification to employee about approval

        $message = $newStatus === 'approved'
            ? 'Resignation request has been fully approved.'
            : 'Resignation request has been approved and forwarded for final approval.';

        return redirect()->route('admin.resignations.index')
            ->with('success', $message);
    }

    /**
     * Reject the resignation request.
     */
    public function reject(Request $request, EmployeeResignation $resignation)
    {
        // Check if resignation belongs to the admin's company
        if ($resignation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if resignation can be rejected
        if (!in_array($resignation->status, ['pending', 'hr_approved', 'manager_approved'])) {
            return redirect()->back()
                ->with('error', 'This resignation request cannot be rejected at this stage.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $isHR = in_array($user->role, ['admin', 'company_admin']);

        $updateData = [
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ];

        if ($isHR) {
            $updateData['hr_remarks'] = $validated['rejection_reason'];
        } else {
            $updateData['manager_remarks'] = $validated['rejection_reason'];
        }

        $resignation->update($updateData);

        // TODO: Send notification to employee about rejection

        return redirect()->route('admin.resignations.index')
            ->with('success', 'Resignation request has been rejected.');
    }

    /**
     * Complete exit interview.
     */
    public function completeExitInterview(Request $request, EmployeeResignation $resignation)
    {
        // Check if resignation belongs to the admin's company
        if ($resignation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($resignation->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Exit interview can only be completed for approved resignations.');
        }

        $validated = $request->validate([
            'exit_interview_date' => 'required|date|before_or_equal:today',
            'exit_interview_remarks' => 'nullable|string|max:1000',
        ]);

        $resignation->update([
            'exit_interview_completed' => true,
            'exit_interview_date' => $validated['exit_interview_date'],
            'hr_remarks' => ($resignation->hr_remarks ? $resignation->hr_remarks . "\n\n" : '') .
                'Exit Interview: ' . ($validated['exit_interview_remarks'] ?? 'Completed on ' . $validated['exit_interview_date'])
        ]);

        return redirect()->back()
            ->with('success', 'Exit interview marked as completed.');
    }

    /**
     * Complete handover process.
     */
    public function completeHandover(Request $request, EmployeeResignation $resignation)
    {
        // Check if resignation belongs to the admin's company
        if ($resignation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($resignation->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Handover can only be completed for approved resignations.');
        }

        $validated = $request->validate([
            'handover_document' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB max
            'handover_remarks' => 'nullable|string|max:1000',
        ]);

        $updateData = ['handover_completed' => true];

        // Handle handover document upload
        if ($request->hasFile('handover_document')) {
            $file = $request->file('handover_document');
            $fileName = 'handover_' . $resignation->employee_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $updateData['handover_document_path'] = $file->storeAs('handover-documents', $fileName, 'public');
        }

        if (isset($validated['handover_remarks'])) {
            $updateData['manager_remarks'] = ($resignation->manager_remarks ? $resignation->manager_remarks . "\n\n" : '') .
                'Handover: ' . $validated['handover_remarks'];
        }

        $resignation->update($updateData);

        return redirect()->back()
            ->with('success', 'Handover process marked as completed.');
    }

    /**
     * Mark assets as returned.
     */
    public function markAssetsReturned(Request $request, EmployeeResignation $resignation)
    {
        // Check if resignation belongs to the admin's company
        if ($resignation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($resignation->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Assets return can only be marked for approved resignations.');
        }

        $validated = $request->validate([
            'asset_ids' => 'nullable|array',
            'asset_ids.*' => 'exists:asset_assignments,id',
            'assets_remarks' => 'nullable|string|max:1000',
        ]);

        // Get assigned assets for the employee
        $assignedAssets = \App\Models\AssetAssignment::where('employee_id', $resignation->employee_id)
            ->whereNull('returned_date')
            ->with('asset')
            ->get();

        // If no assets selected, just mark as returned if all assets are already returned
        if (empty($validated['asset_ids'])) {
            if ($assignedAssets->isEmpty()) {
                // No assets assigned, mark as returned
                $updateData = ['assets_returned' => true];
                if (isset($validated['assets_remarks'])) {
                    $updateData['hr_remarks'] = ($resignation->hr_remarks ? $resignation->hr_remarks . "\n\n" : '') .
                        'Assets Return: ' . $validated['assets_remarks'];
                }
                $resignation->update($updateData);
                return redirect()->back()->with('success', 'Assets marked as returned.');
            } else {
                return redirect()->back()->with('error', 'Please select assets to return.');
            }
        }

        // Mark selected assets as returned
        $assignments = \App\Models\AssetAssignment::whereIn('id', $validated['asset_ids'])->get();

        foreach ($assignments as $assignment) {
            $assignment->update([
                'returned_date' => now(),
                'condition_on_return' => 'returned',
                'notes' => ($assignment->notes ? $assignment->notes . "\n" : '') . ($validated['assets_remarks'] ?? '') . ' - Returned during resignation process'
            ]);

            // Update asset status to available
            $assignment->asset->update(['status' => 'available']);
        }

        // Check if all assigned assets are now returned
        $remainingAssets = \App\Models\AssetAssignment::where('employee_id', $resignation->employee_id)
            ->whereNull('returned_date')
            ->count();

        if ($remainingAssets === 0) {
            $updateData = ['assets_returned' => true];
            if (isset($validated['assets_remarks'])) {
                $updateData['hr_remarks'] = ($resignation->hr_remarks ? $resignation->hr_remarks . "\n\n" : '') .
                    'Assets Return: ' . $validated['assets_remarks'];
            }
            $resignation->update($updateData);
        }

        return redirect()->back()
            ->with('success', 'Selected assets marked as returned.');
    }

    /**
     * Get assigned assets for an employee (for AJAX).
     */
    public function getAssignedAssets(EmployeeResignation $resignation)
    {
        // Check if resignation belongs to the admin's company
        if ($resignation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $assignedAssets = \App\Models\AssetAssignment::where('employee_id', $resignation->employee_id)
            ->whereNull('returned_date')
            ->with('asset')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'asset_name' => $assignment->asset->name ?? 'Unknown Asset',
                    'asset_code' => $assignment->asset->asset_code ?? '',
                    'assigned_date' => $assignment->assigned_date?->format('M d, Y'),
                    'condition_on_assignment' => $assignment->condition_on_assignment,
                ];
            });

        return response()->json($assignedAssets);
    }

    /**
     * Complete final settlement.
     */
    public function completeFinalSettlement(Request $request, EmployeeResignation $resignation)
    {
        // Check if resignation belongs to the admin's company
        if ($resignation->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        if ($resignation->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Final settlement can only be completed for approved resignations.');
        }

        $validated = $request->validate([
            'settlement_amount' => 'nullable|numeric|min:0',
            'settlement_remarks' => 'nullable|string|max:1000',
            'final_settlement_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $updateData = ['final_settlement_completed' => true];

        // Handle final settlement document upload
        if ($request->hasFile('final_settlement_document')) {
            $file = $request->file('final_settlement_document');
            $fileName = 'final_settlement_' . $resignation->employee_id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $updateData['final_settlement_document_path'] = $file->storeAs('final-settlement-documents', $fileName, 'public');
        }

        $settlementInfo = 'Final Settlement: ';
        if (isset($validated['settlement_amount'])) {
            $settlementInfo .= 'Amount: ₹' . number_format($validated['settlement_amount'], 2) . '. ';
        }
        if (isset($validated['settlement_remarks'])) {
            $settlementInfo .= $validated['settlement_remarks'];
        } else {
            $settlementInfo .= 'Completed on ' . now()->format('Y-m-d');
        }

        $updateData['hr_remarks'] = ($resignation->hr_remarks ? $resignation->hr_remarks . "\n\n" : '') . $settlementInfo;

        $resignation->update($updateData);

        return redirect()->back()
            ->with('success', 'Final settlement marked as completed.');
    }

    /**
     * Determine the next approval status based on current status and user role.
     */
    private function determineNextApprovalStatus(EmployeeResignation $resignation, $user)
    {
        $isHR = in_array($user->role, ['admin', 'company_admin']);

        switch ($resignation->status) {
            case 'pending':
                return $isHR ? 'hr_approved' : 'manager_approved';
            case 'hr_approved':
            case 'manager_approved':
                return 'approved';
            default:
                return $resignation->status;
        }
    }
}
