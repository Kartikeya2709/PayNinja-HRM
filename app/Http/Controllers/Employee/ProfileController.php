<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Employee;
use App\Models\Designation;
use App\Models\Department;
use App\Models\EmployeeSalary;
class ProfileController extends Controller
{
    /**
     * Show the employee profile.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $user = Auth::user();
        $employee = $user->employee;

        // dd($employee);
        
      $designation = $employee->designation;
      
      $department = $employee->department;
        // Employee::with('employeeSalaries')->where('id')

        $employeeSalary = EmployeeSalary::where('employee_id', $employee->id)->first();
        
        // Fetch employee documents
        $documents = \App\Models\EmployeeDocument::where('employee_id', $employee->id)->get()->groupBy('type');

        // dd($documents);

        if (!$employee) {
            return redirect()->route('home')->with('error', 'Employee record not found.');
        }

        return view('employee.profile', compact('employee', 'designation', 'department', 'employeeSalary', 'documents'));
    }

    /**
     * Update the employee profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('home')->with('error', 'Employee record not found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
        ]);

        // Update user name
        $user->name = $validated['name'];
        $user->save();

        // Update employee details
        $employee->update([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'date_of_birth' => $validated['date_of_birth'],
            'address' => $validated['address'],
            'emergency_contact_name' => $validated['emergency_contact_name'],
            'emergency_contact_phone' => $validated['emergency_contact_phone'],
            'emergency_contact_relationship' => $validated['emergency_contact_relationship'],
        ]);

        return redirect()->route('employee.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Update the employee's profile image.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateImage(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('home')->with('error', 'Employee record not found.');
        }

        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($employee->profile_image) {
                    Storage::disk('public')->delete($employee->profile_image);
                }

                // Store new image
                $path = $request->file('profile_image')->store('employee/profile-images', 'public');
                
                // Update employee record
                $employee->profile_image = $path;
                $employee->save();

                return redirect()->route('employee.profile')->with('success', 'Profile image updated successfully.');
            }

            return redirect()->route('employee.profile')->with('error', 'No image file uploaded.');
        } catch (\Exception $e) {
            return redirect()->route('employee.profile')->with('error', 'Failed to update profile image. Please try again.');
        }
    }


    /**
     * View the employee profile (read-only).
     *
     * @return \Illuminate\Http\Response
     */
   


} 