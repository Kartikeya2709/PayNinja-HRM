<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the leave types.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $leaveTypes = LeaveType::where('company_id', $companyId)->get();

        return view('company.leave_types.index', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new leave type.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('company.leave_types.create');
    }

    /**
     * Store a newly created leave type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'description' => 'nullable|string',
            'default_days' => 'required|integer|min:0',
            'leave_value_per_cycle' => 'nullable|numeric|min:0',
            'requires_attachment' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;

        LeaveType::create($validated);

        return redirect()->route('company.leave-types.index')
            ->with('success', 'Leave type created successfully.');
    }

    /**
     * Show the form for editing the specified leave type.
     *
     * @param  \App\Models\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveType $leaveType)
    {
        // Check if leave type belongs to the company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('company.leave_types.edit', compact('leaveType'));
    }

    /**
     * Update the specified leave type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LeaveType $leaveType)
    {
        // Check if leave type belongs to the company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($leaveType->id),
            ],
            'description' => 'nullable|string',
            'default_days' => 'required|integer|min:0',
            'leave_value_per_cycle' => 'nullable|numeric|min:0',
            'requires_attachment' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $leaveType->update($validated);

        return redirect()->route('company.leave-types.index')
            ->with('success', 'Leave type updated successfully.');
    }

    /**
     * Remove the specified leave type from storage.
     *
     * @param  \App\Models\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function destroy(LeaveType $leaveType)
    {
        // Check if leave type belongs to the company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if leave type is being used
        if ($leaveType->leaveBalances()->count() > 0 || $leaveType->leaveRequests()->count() > 0) {
            return redirect()->route('company.leave-types.index')
                ->with('error', 'Cannot delete leave type as it is being used.');
        }

        $leaveType->delete();

        return redirect()->route('company.leave-types.index')
            ->with('success', 'Leave type deleted successfully.');
    }
}
