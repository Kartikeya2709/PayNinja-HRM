<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = AssetCategory::where('company_id', Auth::user()->company_id)
            ->withCount('assets')
            ->paginate(10);
        
        return view('assets.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('assets.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $validated['company_id'] = Auth::user()->company_id;

        AssetCategory::create($validated);

        return redirect()->route('assets.categories.index')
            ->with('success', 'Asset category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = AssetCategory::where('company_id', Auth::user()->company_id)
            ->with(['assets' => function($query) {
                $query->latest();
            }])
            ->findOrFail($id);

        return view('assets.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $category = AssetCategory::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);
            
        return view('assets.categories.create', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = AssetCategory::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $category->update($validated);

        return redirect()->route('assets.categories.index')
            ->with('success', 'Asset category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = AssetCategory::where('company_id', Auth::user()->company_id)
            ->findOrFail($id);

        // Check if category has any assets
        if ($category->assets()->exists()) {
            return redirect()->route('assets.categories.index')
                ->with('error', 'Cannot delete category that has assets assigned to it.');
        }

        $category->delete();

        return redirect()->route('assets.categories.index')
            ->with('success', 'Asset category deleted successfully.');
    }
}
