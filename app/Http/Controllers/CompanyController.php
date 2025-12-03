<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index($companyId)
    {
        // Display all employees of a company
        $company = Company::findOrFail($companyId);

        // Get employees from the employees table instead of users
        $employees = Employee::with(['department', 'user'])
            ->where('company_id', $companyId)
            ->get();

        // return view('company.employees.index', compact('company', 'employees'));
    }

    public function create($companyId)
    {
        // Show form to create a new employee for a company
        $company = Company::findOrFail($companyId);
        $departments = Department::where('company_id', $companyId)->get(); // Get departments for the company
        $designations = Designation::where('company_id', $companyId)->get(); // Get designations for the company

        // return view('company.employees.create', compact('company', 'departments', 'designations'));
    }

    public function store(Request $request, $companyId)
    {
        // dd($request->all());
        // Find the company or throw a 404 if not found
        $company = Company::findOrFail($companyId);

        // Validate all data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'phone' => 'nullable|string|max:20',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:255',
            'joining_date' => 'required|date',
            'employment_type' => 'required|in:permanent,contract,intern',
            'address' => 'nullable|string|max:500',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['role'] = 'employee';
        $validated['company_id'] = $company->id;

        // Ensure `company_id` is fillable in User model
        if (!in_array('company_id', (new User())->getFillable())) {
            throw new \Exception("company_id is not fillable in the User model.");
        }

        // Create the new employee (User)
        $user = User::create($validated);

        // Create associated employee details
        EmployeeDetail::create([
            'user_id' => $user->id,
            'dob' => $validated['dob'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'joining_date' => $validated['joining_date'],
            'employment_type' => $validated['employment_type'],
        ]);

        // Create employee record with all necessary fields
        Employee::create([
            'email' => $validated['email'],
            'name' => $validated['name'],
            'user_id' => $user->id,
            'company_id' => $company->id,
            'department_id' => $validated['department_id'],
            'designation_id' => $validated['designation_id'],
            'joining_date' => $validated['joining_date'],
            'employment_type' => $validated['employment_type'],
            'gender' => $validated['gender'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'phone' => $validated['emergency_contact'] ?? null,
            'emergency_contact' => $validated['emergency_contact'] ?? null,
            'address' => $validated['address'] ?? null,
            // 'status' => 'active',
            'created_by' => auth()->id(),
            // 'updated_by' => auth()->id()
        ]);

        // Redirect to the employee list with a success message
        return redirect()->back()->with('success', 'Employee Created Successfully');
    }




    public function edit($companyId, $employeeId)
    {
        // Edit an employee's details
        $company = Company::findOrFail($companyId);
        $employee = User::findOrFail($employeeId);
        $departments = Department::where('company_id', $companyId)->get(); // Get departments for the company
        // return view('company.employees.edit', compact('company', 'employee', 'departments'));
    }

    public function update(Request $request, $companyId, $employeeId)
    {
        // Validate updated data
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,$employeeId",
            'department_id' => 'required|exists:departments,id',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:255',
            'joining_date' => 'nullable|date',
            'employment_type' => 'nullable|in:permanent,contract,intern',
        ]);

        // Update employee details in the `users` table
        $employee = User::findOrFail($employeeId);
        $employee->update($validated);

        // Update or create the employee details
        $employeeDetail = EmployeeDetail::where('user_id', $employeeId)->first();
        if ($employeeDetail) {
            $employeeDetail->update([
                'dob' => $validated['dob'],
                'gender' => $validated['gender'],
                'emergency_contact' => $validated['emergency_contact'],
                'joining_date' => $validated['joining_date'],
                // 'employment_type' => $validated['employment_type'],
            ]);
        } else {
            EmployeeDetail::create([
                'user_id' => $employeeId,
                'dob' => $validated['dob'],
                'gender' => $validated['gender'],
                'emergency_contact' => $validated['emergency_contact'],
                'joining_date' => $validated['joining_date'],
                // 'employment_type' => $validated['employment_type'],
            ]);
        }

        // Redirect to the employee list with success message
        return redirect()->back()->with('success', 'Employee Updated Successfully');
    }

    public function destroy($companyId, $employeeId)
    {
        // Delete an employee and their details
        $employee = User::findOrFail($employeeId);
        $employee->delete();

        // Also delete the employee details
        $employeeDetail = EmployeeDetail::where('user_id', $employeeId)->first();
        if ($employeeDetail) {
            $employeeDetail->delete();
        }

        return redirect()->back()->with('success', 'Employee Deleted Successfully');
    }
}
