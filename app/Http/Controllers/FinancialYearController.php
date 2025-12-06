<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FinancialYearController extends Controller
{
    /**
     * Display a listing of financial years for the company
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->employee->company;
        $financialYears = FinancialYear::where('company_id', $company->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('financial_years.index', compact('company', 'financialYears'));
    }

    /**
     * Show the form for creating a new financial year
     */
    public function create()
    {
        $user = Auth::user();
        $company = $user->employee->company;

        // Check if there's already an active financial year
        $activeFinancialYear = FinancialYear::where('company_id', $company->id)
            ->where('is_active', true)
            ->first();

        return view('financial_years.create', compact('company', 'activeFinancialYear'));
    }

    /**
     * Store a newly created financial year in storage
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $company = $user->employee->company;

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if there's already an active financial year
        $existingActive = FinancialYear::where('company_id', $company->id)
            ->where('is_active', true)
            ->exists();

        if ($existingActive) {
            return redirect()->back()
                ->with('error', 'A financial year is already active. You can only have one active financial year at a time.')
                ->withInput();
        }

        // Create the new financial year
        $financialYear = FinancialYear::create([
            'company_id' => $company->id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => true,
            'is_locked' => false,
        ]);

        Log::info("Financial year created for company {$company->id}: {$financialYear->name}");

        return redirect()->route('company-admin.financial-years.index')
            ->with('success', 'Financial year created successfully!');
    }

    /**
     * Display the specified financial year
     */
    public function show($financialYearId)
    {
        $user = Auth::user();
        $company = $user->employee->company;
        $financialYear = FinancialYear::where('company_id', $company->id)
            ->findOrFail($financialYearId);

        return view('financial_years.show', compact('company', 'financialYear'));
    }

    /**
     * Show the form for editing the specified financial year
     */
    public function edit($financialYearId)
    {
        $user = Auth::user();
        $company = $user->employee->company;
        $financialYear = FinancialYear::where('company_id', $company->id)
            ->findOrFail($financialYearId);

        if ($financialYear->is_locked) {
            return redirect()->back()
                ->with('error', 'This financial year is locked and cannot be modified.');
        }

        return view('financial_years.edit', compact('company', 'financialYear'));
    }

    /**
     * Update the specified financial year in storage
     */
    public function update(Request $request, $financialYearId)
    {
        $user = Auth::user();
        $company = $user->employee->company;
        $financialYear = FinancialYear::where('company_id', $company->id)
            ->findOrFail($financialYearId);

        if ($financialYear->is_locked) {
            return redirect()->back()
                ->with('error', 'This financial year is locked and cannot be modified.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // If this is being set as active, deactivate others
        if ($request->has('is_active') && $request->is_active) {
            FinancialYear::where('company_id', $company->id)
                ->where('id', '!=', $financialYearId)
                ->update(['is_active' => false]);
        }

        $financialYear->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->has('is_active') ? true : $financialYear->is_active,
            'is_locked' => $request->has('is_locked') ? true : $financialYear->is_locked,
        ]);

        Log::info("Financial year updated for company {$company->id}: {$financialYear->name}");

        return redirect()->route('company-admin.financial-years.index')
            ->with('success', 'Financial year updated successfully!');
    }

    /**
     * Remove the specified financial year from storage
     */
    public function destroy($financialYearId)
    {
        $user = Auth::user();
        $company = $user->employee->company;
        $financialYear = FinancialYear::where('company_id', $company->id)
            ->findOrFail($financialYearId);

        if ($financialYear->is_locked) {
            return redirect()->back()
                ->with('error', 'This financial year is locked and cannot be deleted.');
        }

        if ($financialYear->is_active) {
            return redirect()->back()
                ->with('error', 'The active financial year cannot be deleted. Please set another financial year as active first.');
        }

        $financialYear->delete();

        Log::info("Financial year deleted for company {$company->id}: {$financialYear->name}");

        return redirect()->route('company-admin.financial-years.index')
            ->with('success', 'Financial year deleted successfully!');
    }

    /**
     * Activate a financial year
     */
    public function activate($financialYearId)
    {
        $user = Auth::user();
        $company = $user->employee->company;
        $financialYear = FinancialYear::where('company_id', $company->id)
            ->findOrFail($financialYearId);

        // Deactivate all other financial years for this company
        FinancialYear::where('company_id', $company->id)
            ->where('id', '!=', $financialYearId)
            ->update(['is_active' => false]);

        $financialYear->update(['is_active' => true]);

        Log::info("Financial year activated for company {$company->id}: {$financialYear->name}");

        return redirect()->route('company-admin.financial-years.index')
            ->with('success', 'Financial year activated successfully!');
    }

    /**
     * Lock a financial year
     */
    public function lock($financialYearId)
    {
        $user = Auth::user();
        $company = $user->employee->company;
        $financialYear = FinancialYear::where('company_id', $company->id)
            ->findOrFail($financialYearId);

        if ($financialYear->is_locked) {
            return redirect()->back()
                ->with('error', 'This financial year is already locked.');
        }

        $financialYear->update(['is_locked' => true]);

        Log::info("Financial year locked for company {$company->id}: {$financialYear->name}");

        return redirect()->route('company-admin.financial-years.index')
            ->with('success', 'Financial year locked successfully!');
    }

    /**
     * Unlock a financial year
     */
    public function unlock($financialYearId)
    {
        $user = Auth::user();
        $company = $user->employee->company;
        $financialYear = FinancialYear::where('company_id', $company->id)
            ->findOrFail($financialYearId);

        if (!$financialYear->is_locked) {
            return redirect()->back()
                ->with('error', 'This financial year is not locked.');
        }

        $financialYear->update(['is_locked' => false]);

        Log::info("Financial year unlocked for company {$company->id}: {$financialYear->name}");

        return redirect()->route('company-admin.financial-years.index')
            ->with('success', 'Financial year unlocked successfully!');
    }
}
