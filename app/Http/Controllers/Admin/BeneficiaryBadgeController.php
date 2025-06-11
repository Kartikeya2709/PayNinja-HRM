<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BeneficiaryBadge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BeneficiaryBadgeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $beneficiaryBadges = BeneficiaryBadge::where('company_id', $companyId)
            ->orderBy('is_active', 'desc')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(20);
        return view('admin.payroll.beneficiary-badges.index', compact('beneficiaryBadges'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BeneficiaryBadge  $beneficiaryBadge
     * @return \Illuminate\Http\Response
     */
    public function show(BeneficiaryBadge $beneficiaryBadge)
    {
        // Ensure the badge belongs to the user's company
        if ($beneficiaryBadge->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.payroll.beneficiary-badges.show', compact('beneficiaryBadge'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.payroll.beneficiary-badges.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['allowance', 'deduction'])],
            'calculation_type' => ['required', Rule::in(['flat', 'percentage'])],
            'value' => 'required|numeric|min:0',
            'based_on' => 'nullable|required_if:calculation_type,percentage|string|max:255',
            'is_active' => 'sometimes|boolean',
            'description' => 'nullable|string',
        ]);

        $companyId = Auth::user()->company_id;
        if (!$companyId) {
            return redirect()->back()->with('error', 'User is not associated with a company.');
        }

        $badge = BeneficiaryBadge::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'type' => $request->type,
            'calculation_type' => $request->calculation_type,
            'value' => $request->value,
            'based_on' => $request->calculation_type === 'percentage' ? $request->based_on : null,
            'is_active' => $request->boolean('is_active'),
            'is_company_wide' => $request->boolean('is_company_wide'),
            'description' => $request->description,
        ]);

        // If badge is active and company-wide, apply to all employees
        if ($badge->is_active && $badge->is_company_wide) {
            $badge->applyToAllEmployees();
        }

        return redirect()->route('admin.payroll.beneficiary-badges.index')
                         ->with('success', 'Beneficiary badge created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BeneficiaryBadge  $beneficiaryBadge
     * @return \Illuminate\Http\Response
     */
    public function edit(BeneficiaryBadge $beneficiaryBadge)
    {
        // Ensure the badge belongs to the user's company
        if ($beneficiaryBadge->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('admin.payroll.beneficiary-badges.edit', compact('beneficiaryBadge'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BeneficiaryBadge $beneficiaryBadge)
    {
        if ($beneficiaryBadge->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['allowance', 'deduction'])],
            'calculation_type' => ['required', Rule::in(['flat', 'percentage'])],
            'value' => 'required|numeric|min:0',
            'based_on' => 'nullable|required_if:calculation_type,percentage|string|max:255',
            'is_active' => 'sometimes|boolean',
            'is_company_wide' => 'sometimes|boolean',
            'description' => 'nullable|string',
        ]);

        $wasCompanyWide = $beneficiaryBadge->is_company_wide;
        $isNowActive = $request->boolean('is_active');
        $isNowCompanyWide = $request->boolean('is_company_wide');
        
        // Clear any cached data related to this badge before updating
        $this->clearBadgeCache($beneficiaryBadge);

        $beneficiaryBadge->update([
            'name' => $request->name,
            'type' => $request->type,
            'calculation_type' => $request->calculation_type,
            'value' => $request->value,
            'based_on' => $request->calculation_type === 'percentage' ? $request->based_on : null,
            'is_active' => $isNowActive,
            'is_company_wide' => $isNowCompanyWide,
            'description' => $request->description,
        ]);

        // If this is a company-wide badge and it's active, apply it to all employees
        if ($isNowCompanyWide && $isNowActive) {
            $beneficiaryBadge->applyToAllEmployees();
        }

        return redirect()->route('admin.payroll.beneficiary-badges.index')
                         ->with('success', 'Beneficiary badge updated successfully.');
    }
    
    /**
     * Clear any cached data related to a badge
     * 
     * @param BeneficiaryBadge $badge
     * @return void
     */
    protected function clearBadgeCache(BeneficiaryBadge $badge)
    {
        // Clear the badge from cache if it's cached
        \Cache::forget("beneficiary_badge_{$badge->id}");
        
        // Clear any related caches, for example:
        // - Employee badge assignments
        // - Payroll calculations that might have used this badge
        
        // Clear all employee badge assignments cache for this company
        $cacheKey = "employee_badges_company_{$badge->company_id}";
        \Cache::forget($cacheKey);
        
        // Log the cache clearing for debugging
        \Log::debug('Cleared badge-related caches', [
            'badge_id' => $badge->id,
            'company_id' => $badge->company_id,
            'cache_keys_cleared' => [
                "beneficiary_badge_{$badge->id}",
                $cacheKey
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BeneficiaryBadge $beneficiaryBadge)
    {
        if ($beneficiaryBadge->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Consider checking if the badge is in use by any employee before deleting
        // e.g., if ($beneficiaryBadge->employeeBeneficiaryBadges()->exists()) { ... }

        $beneficiaryBadge->delete();

        return redirect()->route('admin.payroll.beneficiary-badges.index')
                         ->with('success', 'Beneficiary badge deleted successfully.');
    }

    /**
     * Apply a company-wide badge to all employees
     *
     * @param \App\Models\BeneficiaryBadge $badge
     * @return \Illuminate\Http\RedirectResponse
     */
    public function applyToAllEmployees(BeneficiaryBadge $badge)
    {
        // if ($badge->company_id !== Auth::user()->company_id) {
        //     abort(403, 'Unauthorized action.');
        // }


        // Ensure the badge has a company ID
        $companyId = $badge->company_id;
        if (!$companyId && Auth::check() && Auth::user()->company_id) {
            // Use the company ID without modifying the original badge
            $badge = clone $badge;
            $badge->company_id = Auth::user()->company_id;
        }

        try {
            $count = $badge->applyToAllEmployees([
                'is_applicable' => true,
                'custom_value' => null,
                'custom_calculation_type' => null,
                'custom_based_on' => null,
                'start_date' => now(),
                'end_date' => null,
            ]);
            
            return redirect()->back()
                ->with('success', "Badge applied to {$count} employees successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to apply badge to all employees: ' . $e->getMessage());
        }
    }

    /**
     * Apply badge to all employees via AJAX
     *
     * @param BeneficiaryBadge $badge
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiApplyToAllEmployees(BeneficiaryBadge $badge)
    {
        // dd($badge->company_id, Auth::user()->company_id);
        // if ($badge->company_id !== Auth::user()->company_id) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        // }

        // Ensure the badge has a company ID
        $companyId = $badge->company_id;
        if (!$companyId) {
            // If no company ID is set, use the authenticated user's company
            if (Auth::check() && Auth::user()->company_id) {
                $companyId = Auth::user()->company_id;
                // Use the company ID without modifying the original badge
                $badge = clone $badge;
                $badge->company_id = $companyId;
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot apply badge: No company associated with this badge and no company found for the current user.'
                ], 400);
            }
        }

        try {
            $count = $badge->applyToAllEmployees([
                'is_applicable' => true,
                'custom_value' => null,
                'custom_calculation_type' => null,
                'custom_based_on' => null,
                'start_date' => now(),
                'end_date' => null,
            ]);
            
            return response()->json([
                'success' => true, 
                'message' => "Badge applied to {$count} employees successfully.",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to apply badge to all employees: ' . $e->getMessage()
            ], 500);
        }
    }

    // The applyBadgeToAllEmployees method has been moved to the BeneficiaryBadge model
    // as the applyToAllEmployees method for better code organization and reusability.
}
