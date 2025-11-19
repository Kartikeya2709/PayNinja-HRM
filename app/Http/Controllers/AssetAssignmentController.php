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
        $assignments = AssetAssignment::with(['asset', 'employee', 'assignedBy'])
            ->whereHas('asset', function ($query) {
                $query->where('company_id', Auth::user()->company_id);
            })
            ->orderBy('assigned_date', 'desc')
            ->paginate(10);

        return view('assets.assignments.index', compact('assignments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $assets = Asset::where('company_id', Auth::user()->company_id)
        ->where('status', 'available')
        ->select('id', 'name', 'asset_code')
        ->get();

        $departments = Department::where('company_id', Auth::user()->company_id)
            ->with(['designations.employees'])
            ->get();
            // Add employees variable
        $employees = Employee::where('company_id', Auth::user()->company_id)->get();
        // dd($employees);

        return view('assets.assignments.create', compact('assets', 'departments', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'employee_id' => 'required|exists:employees,id',
            'assigned_date' => 'required|date',
            'expected_return_date' => 'nullable|date|after:assigned_date',
            'condition_on_assignment' => 'required|in:good,fair,poor,damaged',
            'notes' => 'nullable|string|max:1000',
        ]);

        $assignment = AssetAssignment::create([
            'asset_id' => $request->asset_id,
            'employee_id' => $request->employee_id,
            'assigned_by' => Auth::id(),
            'assigned_date' => $request->assigned_date,
            'expected_return_date' => $request->expected_return_date,
            'condition_on_assignment' => $request->condition_on_assignment,
            'notes' => $request->notes,
            'status' => 'assigned',
        ]);

        // Update asset status to assigned
        $assignment->asset->update(['status' => 'assigned', 'condition' => $request->condition_on_assignment]);

        return redirect()->route('assets.assignments.index')
            ->with('success', 'Asset assigned successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //  $assignment = AssetAssignment::with(['asset', 'employee', 'assignedBy'])->findOrFail($id);
        // return view('assets.assignments.show', compact('assignment'));

        //         $assignment = AssetAssignment::with([
        //         'asset.assignments.employee',
        //         'asset.assignments.assignedBy',
        //         'employee',
        //         'assignedBy'
        //     ])->findOrFail($id);

        //     return view('assets.assignments.show', compact('assignment'));

        // Load assignment with its asset, category, employee, and assignedBy
        $assignment = AssetAssignment::with([
            'asset',                 // eager load asset
            'asset.category',        // eager load category
            'employee',              // eager load employee
            'assignedBy'             // eager load who assigned it
        ])->findOrFail($id);

        // Get all assignments for this asset (across employees)
        $assetassignmenthistory = AssetAssignment::with(['assignedBy', 'employee', 'asset', 'asset.category'])
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
        $assignment = AssetAssignment::findOrFail($id);

        // Only allow deletion if assignment is returned
        if ($assignment->status !== 'returned') {
            return redirect()->back()->with('error', 'Cannot delete an active assignment. Return the asset first.');
        }

        $assignment->delete();

        return redirect()->route('assets.assignments.index')
            ->with('success', 'Asset assignment deleted successfully.');
    }

    /**
     * Return an asset assignment.
     */
    public function returnAsset(Request $request, string $id)
    {
        $request->validate([
            'return_condition' => 'required|in:good,fair,poor,damaged',
            'return_notes' => 'nullable|string|max:1000',
        ]);

        $assignment = AssetAssignment::findOrFail($id);

        $assignment->update([
            'status' => 'returned',
            'returned_date' => now(),
            'condition_on_return' => $request->return_condition,
            'return_notes' => $request->return_notes,
        ]);

        // Update asset status back to available
        $assignment->asset->update(['status' => 'available']);
         $assignment->asset->update(['condition' => $request->return_condition]);

        return redirect()->route('assets.assignments.show', $assignment->id)
            ->with('success', 'Asset returned successfully.');
    }
}
