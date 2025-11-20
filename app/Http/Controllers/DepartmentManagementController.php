<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartmentManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,company_admin']);
    }

    /**
     * Display a listing of the departments.
     */
    public function index()
    {
        $departments = Department::where('company_id', auth()->user()->company_id)->get();
        return view('company.employees.departments.index', compact('departments'));
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('company.employees.departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,NULL,id,company_id,' . auth()->user()->company_id,
            'description' => 'nullable|string'
        ]);

        Department::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->route('departments.index', ['companyId' => auth()->user()->company_id])
            ->with('success', 'Department created successfully.');
    }

    /**
     * Show the form for editing the department.
     */
    public function edit(Department $department)
    {
        if ($department->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }
        return view('company.employees.departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, Department $department)
    {
        if ($department->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id . ',id,company_id,' . auth()->user()->company_id,
            'description' => 'nullable|string'
        ]);

        $department->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->route('departments.index', ['companyId' => auth()->user()->company_id])
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        if ($department->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }
        $department->delete();
        return redirect()->route('departments.index', ['companyId' => auth()->user()->company_id])
            ->with('success', 'Department deleted successfully.');
    }
}
