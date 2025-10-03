<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Department;

class DesignationManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,company_admin']);
    }

    /**
     * Display a listing of the designations.
     */
    public function index()
    {
        $designations = Designation::with('department')
            ->where('company_id', auth()->user()->company_id)
            ->get();

         return view('company.employees.designations.index', compact('designations'));
    }

    /**
     * Show the form for creating a new designation.
     * 
     */
    public function create()
    {
        $departments= Department::with("designations")->get();    
        return view('company.employees.designations.create', compact('departments'));
    }

    /**
     * Store a newly created designation in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:designations,title,NULL,id,company_id,' . auth()->user()->company_id,
            'description' => 'nullable|string',
            'level' => 'required|string|max:255',
            'department_id'=> 'required|exists:departments,id',
        ]);

        Designation::create([
            'company_id' => auth()->user()->company_id,
            'title' => $request->title,
            'description' => $request->description,
            'level' => $request->level,
            'department_id'=>$request->department_id,
        ]);

        return redirect()->route('company.designations.index', ['companyId' => auth()->user()->company_id])
            ->with('success', 'Designation created successfully.');
    }

    /**
     * Show the form for editing the designation.
     */
    public function edit(Designation $designation)
    {
        if ($designation->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }
         // fetch only departments of the same company
        $departments = Department::where('company_id', auth()->user()->company_id)->get();
        return view('company.employees.designations.edit', compact('designation', 'departments'));
    }

    /**
     * Update the specified designation in storage.
     */
    public function update(Request $request, Designation $designation)
    {
        if ($designation->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Validate input
        $request->validate([
            'title' => 'required|string|max:255|unique:designations,title,' . $designation->id . ',id,company_id,' . auth()->user()->company_id,
            'department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string',
            'level' => 'required|string|max:255',
        ]);

        // Update designation
        $designation->update([
            'title' => $request->title,
            'department_id' => $request->department_id,
            'description' => $request->description,
            'level' => $request->level
        ]);

        return redirect()->route('company.designations.index', ['companyId' => auth()->user()->company_id])
            ->with('success', 'Designation updated successfully.');
    }

    /**
     * Remove the specified designation from storage.
     */
    public function destroy(Designation $designation)
    {
        if ($designation->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized action.');
        }
        $designation->delete();
        return redirect()->route('company.designations.index', ['companyId' => auth()->user()->company_id])
            ->with('success', 'Designation deleted successfully.');
    }
}
