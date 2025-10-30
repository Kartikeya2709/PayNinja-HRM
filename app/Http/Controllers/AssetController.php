<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assets = Asset::where('company_id', Auth::user()->company_id)
            ->with(['category', 'currentAssignment.employee'])
            ->latest()
            ->paginate(10);

            // dd($assets);

        return view('assets.index', compact('assets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = AssetCategory::where('company_id', Auth::user()->company_id)
            ->pluck('name', 'id');

        return view('assets.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:asset_categories,id',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'condition' => 'required|in:good,fair,poor,damaged',
            'notes' => 'nullable|string'
        ]);

        $validated['company_id'] = Auth::user()->company_id;
        $validated['asset_code'] = 'AST-' . strtoupper(Str::random(8));
        $validated['status'] = 'available';

        Asset::create($validated);

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
      
        $asset = Asset::where('company_id', Auth::user()->company_id)
            ->with(['category', 'assignments.employee', 'assignments.assignedBy', 'conditions'])
            ->findOrFail($id);
           

        return view('assets.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $asset = Asset::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);
            
        $categories = AssetCategory::where('company_id', Auth::user()->company_id)
            ->pluck('name', 'id');

        return view('assets.create', compact('asset', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $asset = Asset::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:asset_categories,id',
            'purchase_cost' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'condition' => 'required|in:good,fair,poor,damaged',
            'notes' => 'nullable|string'
        ]);

        $asset->update($validated);

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $asset = Asset::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        // Check if asset is currently assigned
        if ($asset->status === 'assigned') {
            return redirect()->route('admin.assets.index')
                ->with('error', 'Cannot delete an asset that is currently assigned.');
        }

        $asset->delete();

        return redirect()->route('admin.assets.index')
            ->with('success', 'Asset deleted successfully.');
    }

    /**
     * Display the employee's assigned assets.
     */
    public function employeeAssets()
    {
        $employee = Auth::user()->employee;

        $assignments = \App\Models\AssetAssignment::where('employee_id', $employee->id)
            ->with(['asset.category'])
            ->orderBy('assigned_date', 'desc')
            ->get();

        return view('employee.assets.index', compact('assignments'));
    }
}
