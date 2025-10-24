<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Department;
use App\Models\Employee;

class AssetAssignmentController extends Controller
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
         $assets = Asset::where('company_id', Auth::user()->company_id)
        ->where('status', 'available')
        ->pluck('name', 'id');

    $departments = Department::where('company_id', Auth::user()->company_id)
        ->with(['designations.employees'])
        ->get();
          // Add employees variable
    $employees = Employee::where('company_id', Auth::user()->company_id)->get();
    // dd($employees);

    return view('assets.assignments.create', compact('assets', 'departments', 'employees'));
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
        //  $assignment = AssetAssignment::with(['asset', 'employee', 'assignedBy'])->findOrFail($id);
        // return view('assets.assignments.show', compact('assignment'));

            //     $assignment = AssetAssignment::with([
            //     'asset.assignments.employee',
            //     'asset.assignments.assignedBy',
            //     'employee',
            //     'assignedBy'
            // ])->findOrFail($id);

            // return view('assets.assignments.show', compact('assignment'));

              // Load assignment with its asset, category, employee, and assignedBy
    $assignment = AssetAssignment::with([
        'asset.category',        // eager load category
        'employee',              // eager load employee
        'assignedBy'             // eager load who assigned it
    ])->findOrFail($id);

    // Get all assignments for this asset (across employees)
    $assetassignmenthistory = AssetAssignment::with(['assignedBy', 'employee'])
                        ->where('asset_id', $assignment->asset_id)
                        ->orderBy('assigned_date', 'desc')
                        ->get();
                        

    // $assethisthis = Asset::with('lastAssignment')->find($assignment->asset_id);

    return view('assets.assignments.show', compact('assignment', 'assetassignmenthistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
