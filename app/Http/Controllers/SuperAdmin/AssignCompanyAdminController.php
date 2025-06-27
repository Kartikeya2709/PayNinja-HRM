<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignCompanyAdminController extends Controller
{
    public function index()
    {
        $admins = Employee::with(['user', 'company'])
            ->whereHas('user', function($q){ $q->where('role', 'company_admin'); })
            ->get();
        return view('superadmin.assigned_company_admins', compact('admins'));
    }

    public function create()
    {
        $users = User::where('role', 'user')->get();
        $companies = Company::all();
        return view('superadmin.assign_company_admin', compact('users', 'companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'phone' => 'nullable|string|max:10',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
        ]);

        // Prevent duplicate company admin assignment
        $alreadyAssigned = Employee::where('company_id', $validated['company_id'])
            ->whereHas('user', function($q){ $q->where('role', 'company_admin'); })
            ->exists();
        if ($alreadyAssigned) {
            return back()->withErrors(['company_id' => 'This company already has a company admin assigned.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($validated['user_id']);
            Log::info('AssignCompanyAdminController@store: User found', ['user_id' => $user->id, 'role' => $user->role]);
            $user->role = 'company_admin';
            $user->company_id = $validated['company_id']; // Store company_id in user
            $user->save();
            Log::info('AssignCompanyAdminController@store: User updated', ['user_id' => $user->id, 'role' => $user->role, 'company_id' => $user->company_id]);

            // Ensure department exists
            $department = \App\Models\Department::firstOrCreate(
                [
                    'name' => 'Company Admin',
                    'company_id' => $validated['company_id']
                ],
                [
                    'description' => 'Company Admin Department'
                ]
            );
            Log::info('AssignCompanyAdminController@store: Department ensured', ['department_id' => $department->id]);
            // Ensure designation exists
            $designation = \App\Models\Designation::firstOrCreate(
                [
                    'title' => 'Company Admin',
                    'company_id' => $validated['company_id']
                ],
                [
                    'description' => 'Company Admin Designation',
                    'level' => 'Admin'
                ]
            );
            Log::info('AssignCompanyAdminController@store: Designation ensured', ['designation_id' => $designation->id]);

            $employee = Employee::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'company_id' => $validated['company_id'],
                ],
                [
                    'department_id' => $department->id,
                    'designation_id' => $designation->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $validated['phone'] ?? null,
                    'dob' => $validated['dob'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'emergency_contact' => $validated['emergency_contact'] ?? null,
                    'current_address' => $validated['address'] ?? null,
                    'joining_date' => now(),
                    'employee_type' => 'Permanent',
                    'created_by' => auth()->user()->id,
                ]
            );
            Log::info('AssignCompanyAdminController@store: Employee updated/created', ['employee_id' => $employee->id]);
            DB::commit();
            return redirect()->route('superadmin.assigned-company-admins.index')->with('success', 'Company admin assigned successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to assign company admin: ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $admin = Employee::with(['user', 'company'])->findOrFail($id);
        $users = User::where('role', 'user')->orWhere('id', $admin->user_id)->get();
        $companies = Company::all();
        return view('superadmin.assign_company_admin', compact('admin', 'users', 'companies'));
    }

    public function update(Request $request, $id)
    {
        $admin = Employee::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'company_id' => 'required|exists:companies,id',
            'phone' => 'nullable|string|max:10',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
        ]);

        // Prevent duplicate company admin assignment (except for current record)
        $alreadyAssigned = Employee::where('company_id', $validated['company_id'])
            ->whereHas('user', function($q){ $q->where('role', 'company_admin'); })
            ->where('id', '!=', $admin->id)
            ->exists();
        if ($alreadyAssigned) {
            return back()->withErrors(['company_id' => 'This company already has a company admin assigned.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $user = User::findOrFail($validated['user_id']);
            Log::info('AssignCompanyAdminController@update: User found', ['user_id' => $user->id, 'role' => $user->role]);
            $user->role = 'company_admin';
            $user->company_id = $validated['company_id']; // Store company_id in user
            $user->save();
            Log::info('AssignCompanyAdminController@update: User updated', ['user_id' => $user->id, 'role' => $user->role, 'company_id' => $user->company_id]);

            // Ensure department exists
            $department = \App\Models\Department::firstOrCreate(
                [
                    'name' => 'Company Admin',
                    'company_id' => $validated['company_id']
                ],
                [
                    'description' => 'Company Admin Department'
                ]
            );
            Log::info('AssignCompanyAdminController@update: Department ensured', ['department_id' => $department->id]);
            // Ensure designation exists
            $designation = \App\Models\Designation::firstOrCreate(
                [
                    'title' => 'Company Admin',
                    'company_id' => $validated['company_id']
                ],
                [
                    'description' => 'Company Admin Designation',
                    'level' => 1
                ]
            );
            Log::info('AssignCompanyAdminController@update: Designation ensured', ['designation_id' => $designation->id]);

            $admin->update([
                'user_id' => $validated['user_id'],
                'company_id' => $validated['company_id'],
                'department_id' => $department->id,
                'designation_id' => $designation->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $validated['phone'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
            Log::info('AssignCompanyAdminController@update: Employee updated', ['employee_id' => $admin->id]);
            DB::commit();
            return redirect()->route('superadmin.assigned-company-admins.index')->with('success', 'Company admin updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update company admin: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $admin = Employee::findOrFail($id);
        DB::beginTransaction();
        try {
            $user = $admin->user;
            $user->role = 'employee'; // Or previous role
            $user->save();
            $admin->delete();
            DB::commit();
            return redirect()->route('superadmin.assigned-company-admins.index')->with('success', 'Company admin assignment removed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to remove company admin: ' . $e->getMessage()]);
        }
    }
}
