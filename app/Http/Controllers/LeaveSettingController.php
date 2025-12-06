<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class LeaveSettingController extends Controller
{
    /**
     * Display the leave settings for a specific leave type.
     *
     * @param  \App\Models\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function edit(LeaveType $leaveType)
    {
        // Check if leave type belongs to the company
        // if ($leaveType->company_id !== Auth::user()->company_id) {
        //     abort(403, 'Unauthorized action.');
        // }

        return view('company.leave_settings.edit', compact('leaveType'));
    }

    /**
     * Update the leave settings for a specific leave type.
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

        $validated = $request->validate([
            // Monthly and yearly limits
            'monthly_limit' => 'nullable|integer|min:0',
            'yearly_limit' => 'nullable|integer|min:0',

            // Disbursement cycle settings
            'disbursement_cycle' => 'required|in:monthly,quarterly,half_yearly,yearly',
            'disbursement_time' => 'required|in:start_of_cycle,end_of_cycle',

            // Carry forward settings
            'enable_carry_forward' => 'boolean',
            'max_carry_forward_days' => 'nullable|integer|min:0',
            'allow_carry_forward_to_next_year' => 'boolean',
            'yearly_carry_forward_limit' => 'nullable|integer|min:0',
            'leave_value_per_cycle' => 'nullable|numeric|min:0',

            // Half day settings
            'allow_half_day_leave' => 'boolean',
            'allow_negative_balance' => 'boolean',
            'half_day_deduction_priority' => 'required|in:full_day_first,half_day_first',
        ]);

        $leaveType->update($validated);

        return redirect()->route('company.leave-types.index')
            ->with('success', 'Leave settings updated successfully.');
    }

    /**
     * Show the leave settings configuration page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $leaveTypes = LeaveType::where('company_id', $companyId)->get();

        return view('company.leave_settings.index', compact('leaveTypes'));
    }

    /**
     * Get leave settings for a specific leave type (API endpoint).
     *
     * @param  \App\Models\LeaveType  $leaveType
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings(LeaveType $leaveType)
    {
        // Check if leave type belongs to the company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        return response()->json([
            'monthly_limit' => $leaveType->monthly_limit,
            'yearly_limit' => $leaveType->yearly_limit,
            'disbursement_cycle' => $leaveType->disbursement_cycle,
            'disbursement_time' => $leaveType->disbursement_time,
            'enable_carry_forward' => $leaveType->enable_carry_forward,
            'max_carry_forward_days' => $leaveType->max_carry_forward_days,
            'allow_carry_forward_to_next_year' => $leaveType->allow_carry_forward_to_next_year,
            'yearly_carry_forward_limit' => $leaveType->yearly_carry_forward_limit,
            'allow_half_day_leave' => $leaveType->allow_half_day_leave,
            'allow_negative_balance' => $leaveType->allow_negative_balance,
            'half_day_deduction_priority' => $leaveType->half_day_deduction_priority,
        ]);
    }
}
