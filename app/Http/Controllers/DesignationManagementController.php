<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\Department;

class DesignationManagementController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['auth', 'role:admin,company_admin']);
    }

    /**
     * Get designation from encrypted ID.
     */
    private function getDesignationFromEncryptedId(string $encryptedId): Designation
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            return Designation::where('company_id', auth()->user()->company_id)->findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
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
        $departments= Department::with("designations") ->where('company_id', auth()->user()->company_id)->get();
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

        // return redirect()->route('designations.index', ['companyId' => auth()->user()->company_id])
        //     ->with('success', 'Designation created successfully.');

        return redirect()->route('designations.index')
            ->with('success', 'Designation created successfully.');
    }

    /**
     * Show the form for editing the designation.
     */
    public function edit(string $encryptedId)
    {
        $designation = $this->getDesignationFromEncryptedId($encryptedId);

         // fetch only departments of the same company
        $departments = Department::where('company_id', auth()->user()->company_id)->get();
        return view('company.employees.designations.edit', compact('designation', 'departments'));
    }

    /**
     * Update the specified designation in storage.
     */
    public function update(Request $request, string $encryptedId)
    {
        $designation = $this->getDesignationFromEncryptedId($encryptedId);

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

            // Redirect back with success message
            return redirect()->route('designations.index')
                            ->with('success', 'Designation updated successfully.');
    }

    /**
     * Remove the specified designation from storage.
     */
    public function destroy(string $encryptedId)
    {
        $designation = $this->getDesignationFromEncryptedId($encryptedId);
        $designation->delete();
        return redirect()->route('designations.index')->with('success', 'Designation deleted successfully.');
    }
}
