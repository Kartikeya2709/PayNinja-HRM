<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (Auth::user()->role !== 'superadmin') {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $roles = Role::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($status, function ($query) use ($status) {
                $query->where('is_active', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('superadmin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $slugs = Slug::with('children')->root()->orderBy('sort_order')->get();
        return view('superadmin.roles.create', compact('slugs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:true,false',
            'company_id' => 'required|exists:companies,id',
            'is_default' => 'boolean',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed. Please check the form.');
        }

        try {
            $permissions = $request->permissions ? str_replace('\\', '', json_encode($request->permissions)) : null;

            Role::create([
                'name' => $request->name,
                'permissions' => $permissions,
                'company_id' => $request->company_id,
                'is_default' => $request->boolean('is_default', false),
                'status' => $request->status,
            ]);

            return redirect()->route('superadmin.roles.index')->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while creating the role: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::findOrFail($id);
        return view('superadmin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $slugs = Slug::with('children')->root()->orderBy('sort_order')->get();
        return view('superadmin.roles.edit', compact('role', 'slugs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role = Role::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:true,false',
            'company_id' => 'required|exists:companies,id',
            'is_default' => 'boolean',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed. Please check the form.');
        }

        try {
            $permissions = $request->permissions ? str_replace('\\', '', json_encode($request->permissions)) : null;

            $role->update([
                'name' => $request->name,
                'permissions' => $permissions,
                'company_id' => $request->company_id,
                'is_default' => $request->boolean('is_default', false),
                'status' => $request->status,
            ]);

            return redirect()->route('superadmin.roles.index')->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while updating the role: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete(); // Soft delete

            return redirect()->route('superadmin.roles.index')->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while deleting the role: ' . $e->getMessage());
        }
    }
}
