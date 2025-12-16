<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SlugController extends Controller
{
    /**
     * Display a listing of slugs with pagination and search.
     */
    public function slugs(Request $request)
    {
        $search = $request->get('search');

        $slugs = Slug::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10);

        $slug_list = Slug::visible()->get();

        return view('superadmin.settings.slug.index', compact('slugs', 'slug_list'));
    }

    /**
     * Store a newly created slug.
     */
    public function add_slug(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'slug' => 'required|string|max:100|unique:slugs,slug',
                'icon' => 'nullable|string|max:100',
                'parent' => 'nullable|numeric|exists:slugs,id',
                'is_visible' => 'required|numeric|in:0,1',
                'sort_order' => 'required|numeric|min:0|max:255',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Validation failed. Please check the form.');
            }

            Slug::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'icon' => $request->icon,
                'parent_id' => $request->parent ?: null,
                'is_visible' => $request->is_visible,
                'sort_order' => $request->sort_order,
            ]);

            return redirect()->back()->with('success', 'Slug created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the slug: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified slug or handle POST update.
     */
    public function edit_slug(Request $request, $slug_id)
    {
        $slug = Slug::findOrFail($slug_id);

        if ($request->isMethod('post')) {
            // Handle POST request for updating
            try {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|string|max:100',
                    'slug' => 'required|string|max:100|unique:slugs,slug,' . $slug->id,
                    'icon' => 'nullable|string|max:100',
                    'parent' => 'nullable|numeric|exists:slugs,id|not_in:' . $slug->id,
                    'is_visible' => 'required|numeric|in:0,1',
                    'sort_order' => 'required|numeric|min:0|max:255',
                ]);

                if ($validator->fails()) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'errors' => $validator->errors()
                        ], 422);
                    }
                    return redirect()->back()
                        ->withErrors($validator)
                        ->withInput()
                        ->with('error', 'Validation failed. Please check the form.');
                }

                $slug->update([
                    'name' => $request->name,
                    'slug' => $request->slug,
                    'icon' => $request->icon,
                    'parent_id' => $request->parent ?: null,
                    'is_visible' => $request->is_visible,
                    'sort_order' => $request->sort_order,
                ]);

                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Slug updated successfully.'
                    ]);
                }

                return redirect()->back()->with('success', 'Slug updated successfully.');

            } catch (\Exception $e) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'An error occurred while updating the slug: ' . $e->getMessage()
                    ], 500);
                }
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'An error occurred while updating the slug: ' . $e->getMessage());
            }
        }

        // Handle GET request for showing edit form
        $slug_list = Slug::visible()
            ->where('id', '!=', $slug->id)
            ->get();

        if ($request->ajax()) {
            return view('superadmin.settings.slug.edit_form', compact('slug', 'slug_list'))->render();
        }

        return view('superadmin.settings.slug.edit', compact('slug', 'slug_list'));
    }

    /**
     * Remove the specified slug (soft delete).
     */
    public function destroy_slug(Request $request, $slug_id)
    {
        try {
            $slug = Slug::findOrFail($slug_id);
            $slug->delete(); // Soft delete

            return redirect()->back()->with('success', 'Slug deleted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while deleting the slug: ' . $e->getMessage());
        }
    }
}
