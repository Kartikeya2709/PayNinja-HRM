<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PayrollSetting;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PayrollSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the payroll settings for the company.
     */
    public function edit()
    {
        // if (!Gate::allows('manage_payroll_settings')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $company = Auth::user()->company;
        if (!$company) {
            // Handle case where admin might not be associated with a company (e.g., Super Admin)
            // Or redirect with an error if company context is strictly required.
            return redirect()->route('home')->with('error', 'Company context not found.');
        }

        $settings = PayrollSetting::firstOrNew(['company_id' => $company->id]);

        // Ensure default values if new, especially for arrays to avoid view errors
        if (!$settings->exists) {
            $settings->deductible_leave_type_ids = [];
            $settings->days_in_month = 30; // Default to 30 days in month
            $settings->enable_reimbursement = false; // Default reimbursement disabled
            // You can set other defaults here if needed, e.g.:
            // $settings->late_arrival_threshold = config('payroll.late_arrival_threshold', 3);
            // $settings->late_arrival_deduction_days = config('payroll.late_arrival_deduction_days', 0.5);
        } else {
            // Ensure boolean fields are properly cast
        }

        $leaveTypes = LeaveType::where('company_id', $company->id)->orWhereNull('company_id')->get(); // Company specific and global leave types

        return view('admin.payroll.settings.edit', compact('settings', 'leaveTypes', 'company'));
    }

    /**
     * Update the payroll settings for the company in storage.
     */
    public function update(Request $request)
    {
        if (!Gate::allows('manage_payroll_settings')) {
            abort(403, 'Unauthorized action.');
        }

        $company = Auth::user()->company;
        if (!$company) {
            return redirect()->route('home')->with('error', 'Company context not found.');
        }

        $validatedData = $request->validate([
            'deductible_leave_type_ids' => 'nullable|array',
            'deductible_leave_type_ids.*' => 'exists:leave_types,id', // Ensure selected IDs exist
            'late_arrival_threshold' => 'nullable|integer|min:0',
            'late_arrival_deduction_days' => 'nullable|numeric|min:0|max:30', // Max 30 days deduction
            // 'days_in_month' => 'required|integer|min:1|max:31', // Days in month for payroll calculations
            'enable_reimbursement' => 'nullable|boolean' // Enable reimbursement in payroll calculations
        ]);

        // Handle checkbox conversion (checkbox only submits value when checked)
        $validatedData['enable_reimbursement'] = $request->has('enable_reimbursement') ? true : false;

        // Ensure deductible_leave_type_ids is an empty array if not provided or null, to prevent DB errors with JSON field
        $validatedData['deductible_leave_type_ids'] = $validatedData['deductible_leave_type_ids'] ?? [];
        
        // Get the current settings to compare changes
        $currentSettings = PayrollSetting::firstOrNew(['company_id' => $company->id]);

        // Update or create the settings
        PayrollSetting::updateOrCreate(
            ['company_id' => $company->id],
            $validatedData
        );

        return redirect()->route('admin.payroll.settings.edit')
            ->with('success', 'Payroll settings updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
