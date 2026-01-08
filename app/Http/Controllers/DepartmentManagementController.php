<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class DepartmentManagementController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['auth', 'role:admin,company_admin']);
    }

    /**
     * Get department from encrypted ID.
     */
    private function getDepartmentFromEncryptedId(string $encryptedId): Department
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            return Department::where('company_id', auth()->user()->company_id)->findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
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

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    /**
     * Show the form for editing the department.
     */
    public function edit(string $encryptedId)
    {
        $department = $this->getDepartmentFromEncryptedId($encryptedId);
        return view('company.employees.departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(Request $request, string $encryptedId)
    {
        $department = $this->getDepartmentFromEncryptedId($encryptedId);

        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id . ',id,company_id,' . auth()->user()->company_id,
            'description' => 'nullable|string'
        ]);

        $department->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(string $encryptedId)
    {
        $department = $this->getDepartmentFromEncryptedId($encryptedId);
        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }
}
