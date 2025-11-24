<?php

namespace App\Http\Controllers\CompanyAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user       = Auth::user();
        $companyId  = $user->company_id;

        $search     = $request->get('search');
        $isActive   = $request->get('is_active');

        $roles = Role::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($isActive !== null && $isActive !== '', function ($query) use ($isActive) {
                $query->where('is_active', $isActive);
            })
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Handle AJAX requests for real-time filtering
        if ($request->ajax()) {
            return response()->json([
                'html' => view('company-admin.roles._table', compact('roles'))->render(),
                'pagination' => $roles->links('pagination::bootstrap-5')->render()
            ]);
        }

        return view('company-admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user           = Auth::user();
        $companyId      = $user->company_id;
        $availableSlugs = [];
            
        // Get company_admin's permissions from their role
        $companyAdminRole = Role::where('is_active', true)
            // ->whereJsonContains('permissions->company_admin', true)
            ->find($user->role_id);
        
        if ($companyAdminRole) {
            $adminPermissions = json_decode($companyAdminRole->permissions ?? '{}', true);
            
            // Filter slugs to only include those the company_admin has access to
            $slugs = Slug::with('children')
                ->root()
                ->orderBy('sort_order')
                ->get()
                ->filter(function ($slug) use ($adminPermissions) {
                    // Include parent slug if admin has access to it
                    if (isset($adminPermissions[$slug->slug]) && $adminPermissions[$slug->slug]) {
                        return true;
                    }
                    
                    // Include parent slug if any child is accessible
                    if ($slug->children) {
                        foreach ($slug->children as $child) {
                            if (isset($adminPermissions[$child->slug]) && $adminPermissions[$child->slug]) {
                                return true;
                            }
                        }
                    }
                    
                    return false;
                })
                ->map(function ($slug) use ($adminPermissions) {
                    // Filter children to only accessible ones
                    if ($slug->children) {
                        $slug->children = $slug->children->filter(function ($child) use ($adminPermissions) {
                            return isset($adminPermissions[$child->slug]) && $adminPermissions[$child->slug];
                        });
                    }
                    return $slug;
                });
        } else {
            // No role found or no permissions, show empty
            $slugs = collect();
        }

        return view('company-admin.roles.create', compact('slugs', 'companyId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $availablePermissions = [];
            
        // Get company_admin's permissions from their role
        $companyAdminRole = Role::where('is_active', true)
            // ->whereJsonContains('permissions->company_admin', true)
            ->find($user->role_id);
        
        if ($companyAdminRole) {
            $availablePermissions = json_decode($companyAdminRole->permissions ?? '{}', true);
        } else {
            $availablePermissions = [];
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,NULL,id,company_id,' . $companyId,
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:true,false'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed. Please check the form.');
        }

        try {
            $requestedPermissions = $request->permissions ?? [];
            
            // Validate that company_admin can only assign permissions they have            
            foreach ($requestedPermissions as $permission => $value) {
                if ($value && !isset($availablePermissions[$permission])) {
                    $invalidPermissions[] = $permission;
                }
            }
            
            if (!empty($invalidPermissions)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'You can only assign permissions that you have access to. Invalid permissions: ' . implode(', ', $invalidPermissions));
            }

            $permissions = $request->permissions ? str_replace('\\', '', json_encode($request->permissions)) : null;

            Role::create([
                'name' => $request->name,
                'permissions' => $permissions,
                'company_id' => $companyId,
                'is_active' => $request->boolean('is_active'),
                'level' => 'company',
            ]);

            return redirect()->route('company-admin.roles.index')->with('success', 'Role created successfully.');
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
        $user = Auth::user();
        $companyId = $user->company_id;

        $role = Role::where('company_id', $companyId)->findOrFail($id);
        return view('company-admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $role = Role::where('company_id', $companyId)->findOrFail($id);
        
        // Get available slugs based on user permissions
        $companyAdminRole = Role::where('is_active', true)
            // ->whereJsonContains('permissions->company_admin', true)
            ->find($user->role_id);
        
        if ($companyAdminRole) {
            $adminPermissions = json_decode($companyAdminRole->permissions ?? '{}', true);
            
            // Filter slugs to only include those the company_admin has access to
            $slugs = Slug::with('children')
                ->root()
                ->orderBy('sort_order')
                ->get()
                ->filter(function ($slug) use ($adminPermissions) {
                    // Include parent slug if admin has access to it
                    if (isset($adminPermissions[$slug->slug]) && $adminPermissions[$slug->slug]) {
                        return true;
                    }
                    
                    // Include parent slug if any child is accessible
                    if ($slug->children) {
                        foreach ($slug->children as $child) {
                            if (isset($adminPermissions[$child->slug]) && $adminPermissions[$child->slug]) {
                                return true;
                            }
                        }
                    }
                    
                    return false;
                })
                ->map(function ($slug) use ($adminPermissions) {
                    // Filter children to only accessible ones
                    if ($slug->children) {
                        $slug->children = $slug->children->filter(function ($child) use ($adminPermissions) {
                            return isset($adminPermissions[$child->slug]) && $adminPermissions[$child->slug];
                        });
                    }
                    return $slug;
                });
        } else {
            // No role found or no permissions, show empty
            $slugs = collect();
        }
        
        return view('company-admin.roles.edit', compact('role', 'slugs', 'companyId'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        $availablePermissions = [];
        
        // Get company_admin's permissions from their role
        $companyAdminRole = Role::where('is_active', true)
            // ->whereJsonContains('permissions->company_admin', true)
            ->find($user->role_id);
        
        if ($companyAdminRole) {
            $availablePermissions = json_decode($companyAdminRole->permissions ?? '{}', true);
        } else {
            $availablePermissions = [];
        }

        $role = Role::where('company_id', $companyId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id . ',id,company_id,' . $companyId,
            'permissions' => 'nullable|array',
            'permissions.*' => 'in:true,false'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Validation failed. Please check the form.');
        }

        try {
            $requestedPermissions = $request->permissions ?? [];
            
            // Validate that user can only assign permissions they have
            $invalidPermissions = [];
            foreach ($requestedPermissions as $permission => $value) {
                if ($value && !isset($availablePermissions[$permission])) {
                    $invalidPermissions[] = $permission;
                }
            }
            
            if (!empty($invalidPermissions)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'You can only assign permissions that you have access to. Invalid permissions: ' . implode(', ', $invalidPermissions));
            }

            $permissions = $request->permissions ? str_replace('\\', '', json_encode($request->permissions)) : null;

            $role->update([
                'name' => $request->name,
                'permissions' => $permissions,
                'company_id' => $companyId,
                'is_active' => $request->boolean('is_active'),
            ]);

            return redirect()->route('company-admin.roles.index')->with('success', 'Role updated successfully.');
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
            $user = Auth::user();
            $companyId = $user->company_id;

            $role = Role::where('company_id', $companyId)->findOrFail($id);
            
            // Check if this is the only role or if it's a default role
            $companyRolesCount = Role::where('company_id', $companyId)->count();
            if ($companyRolesCount <= 1) {
                return redirect()->back()->with('error', 'Cannot delete the last remaining role.');
            }

            $role->delete(); // Soft delete

            return redirect()->route('company-admin.roles.index')->with('success', 'Role deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while deleting the role: ' . $e->getMessage());
        }
    }
}