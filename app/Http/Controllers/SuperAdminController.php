<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function index()
    {
        $companies = Company::withoutGlobalScopes()->with('admin')->get();
        $users = User::with('company')->get();
        return view('superadmin.companies.index', compact('users', 'companies'));
    }

    public function create()
    {
        $users = User::all();
        return view('superadmin.companies.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies',
            'domain' => 'required|url|max:255|unique:companies',
            'phone' => 'required|numeric|digits:10|unique:companies',
            'address' => 'required|string',
            // 'admin_id' => 'required|exists:users,id',
        ]);

        $validated['created_by'] = Auth::user()->id;
        $company = Company::create($validated);

        // $admin = User::find($validated['admin_id']);
        // $admin->company_id = $company->id;
        // $admin->role = 'company_admin';
        // $admin->save();

        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company Created Successfully');
    }

    public function edit($id)
    {
        $company = Company::findOrFail($id);
        // $users = User::all();
        return view('superadmin.companies.edit', compact('company'));
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email,' . $company->id,
            'domain' => 'required|url|max:255|unique:companies,domain,' . $company->id,
            'phone' => 'required|numeric|digits:10|unique:companies,phone,' . $company->id,
            'address' => 'required|string',
            // 'admin_id' => 'required|exists:users,id',
        ]);

        $validated['created_by'] = Auth::user()->id;
        $company->update($validated);

        // $admin = User::find($validated['admin_id']);
        // $admin->company_id = $company->id;
        // $admin->role = 'company_admin';
        // $admin->save();

        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company Updated Successfully');
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
        return redirect()->route('superadmin.companies.index')
            ->with('success', 'Company Deleted Successfully');
    }

    public function show($id)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($id);

        // Get entities and their counts
        $companyAdmins = \App\Models\Employee::with('user')
            ->withoutGlobalScopes()
            ->whereHas('user', function($q) {
                $q->where('role', 'company_admin');
            })
            ->where('company_id', $id)
            ->get();

        $admins = \App\Models\Employee::with('user')
            ->withoutGlobalScopes()
            ->whereHas('user', function($q) {
                $q->where('role', 'admin');
            })
            ->where('company_id', $id)
            ->get();

        $employees = \App\Models\Employee::with(['user', 'department', 'designation'])
            ->withoutGlobalScopes()
            ->whereHas('user', function($q) {
                $q->where('role', 'employee');
            })
            ->where('company_id', $id)
            ->get();

        $departments = \App\Models\Department::where('company_id', $id)
            ->withCount('employees')
            ->get();

        $designations = \App\Models\Designation::where('company_id', $id)
            ->withCount('employees')
            ->get();

        // Get counts for summary cards
        $companyAdminsCount = $companyAdmins->count();
        $adminsCount = $admins->count();
        $employeesCount = $employees->count();
        $departmentsCount = $departments->count();
        $designationsCount = $designations->count();

        return view('superadmin.companies.show', compact(
            'company',
            'companyAdmins',
            'admins',
            'employees',
            'departments',
            'designations',
            'companyAdminsCount',
            'adminsCount',
            'employeesCount',
            'departmentsCount',
            'designationsCount'
        ));
    }

    /**
     * Deactivate the entire company and all its users and employees.
     */
    public function deactivateCompany($id)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($id);

        // Deactivate company
        $company->is_active = false;
        $company->save();

        // Deactivate all users in the company
        User::withoutGlobalScopes()->where('company_id', $company->id)->update(['is_active' => false]);

        // Deactivate all employees in the company
        Employee::withoutGlobalScopes()->where('company_id', $company->id)->update(['is_active' => false]);

        return redirect()->back()->with('success', 'Company and all its users deactivated successfully.');
    }

    /**
     * Activate the entire company and all its users and employees.
     */
    public function activateCompany($id)
    {
        $company = Company::withoutGlobalScopes()->findOrFail($id);

        // Activate company
        $company->is_active = true;
        $company->save();

        // Activate all users in the company
        User::withoutGlobalScopes()->where('company_id', $company->id)->update(['is_active' => true]);

        // Activate all employees in the company
        Employee::withoutGlobalScopes()->where('company_id', $company->id)->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Company and all its users activated successfully.');
    }

    /**
     * Deactivate a specific employee.
     */
    public function deactivateEmployee($companyId, $employeeId)
    {
        $employee = Employee::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($employeeId);
        $user = $employee->user;

        $employee->is_active = false;
        $employee->save();

        if ($user) {
            $user->is_active = false;
            $user->save();
        }

        return redirect()->back()->with('success', 'Employee deactivated successfully.');
    }

    /**
     * Activate a specific employee.
     */
    public function activateEmployee($companyId, $employeeId)
    {
        $employee = Employee::withoutGlobalScopes()->where('company_id', $companyId)->findOrFail($employeeId);
        $user = $employee->user;

        $employee->is_active = true;
        $employee->save();

        if ($user) {
            $user->is_active = true;
            $user->save();
        }

        return redirect()->back()->with('success', 'Employee activated successfully.');
    }
}
