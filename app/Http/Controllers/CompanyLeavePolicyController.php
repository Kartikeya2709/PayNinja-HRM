<?php

namespace App\Http\Controllers;

use App\Models\CompanyLeavePolicy;
use App\Models\FinancialYear;
use App\Models\LeaveType;
use App\Models\LeaveTypePolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CompanyLeavePolicyController extends Controller
{
    /**
     * Display a listing of the leave policies.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $policies = CompanyLeavePolicy::where('company_id', $companyId)
            ->with('financialYear')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('company.leave_policies.index', compact('policies'));
    }

    /**
     * Show the form for creating a new leave policy.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companyId = Auth::user()->company_id;
        $financialYears = FinancialYear::where('company_id', $companyId)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('company.leave_policies.create', compact('financialYears'));
    }

    /**
     * Store a newly created leave policy in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'financial_year_id' => [
                'required',
                'integer',
                Rule::exists('financial_years', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('company_leave_policies')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;

        CompanyLeavePolicy::create($validated);

        return redirect()->route('company.leave-policies.index')
            ->with('success', 'Leave policy created successfully.');
    }

    /**
     * Show the form for editing the specified leave policy.
     *
     * @param  \App\Models\CompanyLeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyLeavePolicy $leavePolicy)
    {
        // Check if policy belongs to the company
        if ($leavePolicy->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $companyId = Auth::user()->company_id;
        $financialYears = FinancialYear::where('company_id', $companyId)
            ->orderBy('start_date', 'desc')
            ->get();

        $leavePolicy->load('leaveTypePolicies.leaveType');

        return view('company.leave_policies.edit', compact('leavePolicy', 'financialYears'));
    }

    /**
     * Update the specified leave policy in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanyLeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CompanyLeavePolicy $leavePolicy)
    {
        // Check if policy belongs to the company
        if ($leavePolicy->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'financial_year_id' => [
                'required',
                'integer',
                Rule::exists('financial_years', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('company_leave_policies')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($leavePolicy->id),
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $leavePolicy->update($validated);

        return redirect()->route('company.leave-policies.index')
            ->with('success', 'Leave policy updated successfully.');
    }

    /**
     * Remove the specified leave policy from storage.
     *
     * @param  \App\Models\CompanyLeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompanyLeavePolicy $leavePolicy)
    {
        // Check if policy belongs to the company
        if ($leavePolicy->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $leavePolicy->delete();

        return redirect()->route('company.leave-policies.index')
            ->with('success', 'Leave policy deleted successfully.');
    }

    /**
     * Show form to manage leave types for a policy.
     *
     * @param  \App\Models\CompanyLeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function manageLeaveTypes(CompanyLeavePolicy $leavePolicy)
    {
        // Check if policy belongs to the company
        if ($leavePolicy->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $companyId = Auth::user()->company_id;
        $leavePolicy->load('leaveTypePolicies.leaveType');

        // Get all active leave types for the company not yet added to this policy
        $availableLeaveTypes = LeaveType::where('company_id', $companyId)
            ->where('is_active', true)
            ->whereNotIn('id', $leavePolicy->leaveTypePolicies()->pluck('leave_type_id')->toArray())
            ->get();

        return view('company.leave_policies.manage-leave-types', compact('leavePolicy', 'availableLeaveTypes'));
    }

    /**
     * Add a leave type to the policy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CompanyLeavePolicy  $leavePolicy
     * @return \Illuminate\Http\Response
     */
    public function addLeaveType(Request $request, CompanyLeavePolicy $leavePolicy)
    {
        // Check if policy belongs to the company
        if ($leavePolicy->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'leave_type_id' => [
                'required',
                'integer',
                Rule::exists('leave_types', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'allocated_days' => 'required|integer|min:0',
            'min_days' => 'nullable|integer|min:0',
        ]);

        // Check if leave type is already added to this policy
        if ($leavePolicy->leaveTypePolicies()->where('leave_type_id', $validated['leave_type_id'])->exists()) {
            return redirect()->back()
                ->with('error', 'Leave type is already added to this policy.');
        }

        LeaveTypePolicy::create([
            'company_leave_policy_id' => $leavePolicy->id,
            'leave_type_id' => $validated['leave_type_id'],
            'allocated_days' => $validated['allocated_days'],
            'min_days' => $validated['min_days'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->route('company.leave-policies.manage-leave-types', $leavePolicy->id)
            ->with('success', 'Leave type added to policy successfully.');
    }

    /**
     * Update a leave type policy.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveTypePolicy  $leaveTypePolicy
     * @return \Illuminate\Http\Response
     */
    public function updateLeaveType(Request $request, LeaveTypePolicy $leaveTypePolicy)
    {
        // Check if policy belongs to the company
        if ($leaveTypePolicy->companyLeavePolicy->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'allocated_days' => 'required|integer|min:0',
            'min_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $leaveTypePolicy->update($validated);

        return redirect()->back()
            ->with('success', 'Leave type policy updated successfully.');
    }

    /**
     * Remove a leave type from a policy.
     *
     * @param  \App\Models\LeaveTypePolicy  $leaveTypePolicy
     * @return \Illuminate\Http\Response
     */
    public function removeLeaveType(LeaveTypePolicy $leaveTypePolicy)
    {
        // Check if policy belongs to the company
        if ($leaveTypePolicy->companyLeavePolicy->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $policyId = $leaveTypePolicy->company_leave_policy_id;
        $leaveTypePolicy->delete();

        return redirect()->route('company.leave-policies.manage-leave-types', $policyId)
            ->with('success', 'Leave type removed from policy successfully.');
    }
}
