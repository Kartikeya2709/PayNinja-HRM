<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssignCompanyAdminController extends Controller
{
    public function index()
    {
        $admins = Employee::with(['user', 'company'])
            ->whereHas('user', function($q) {
                $q->where('role', 'company_admin');
            })
            ->get();

        return view('superadmin.assigned_company_admins', compact('admins'));
    }

    public function create()
    {
        // Pre-load companies with comprehensive data for dynamic display
        $companies = Company::with([
            'employees.user', // Load employees with their user relationships
            'users',
            'departments',
            'designations',
            'documents'
        ])->get();

        // Get assigned company IDs for optimization
        $assignedCompanies = $companies->filter(function($company) {
            $adminCount = $company->employees->filter(function($employee) {
                return $employee->user && $employee->user->role === 'company_admin';
            })->count();
            return $adminCount > 0;
        })->pluck('id')->toArray();

        // Get existing users for potential reassignment (if implementing user selection)
        $users = User::where('role', 'user')->get();

        // Get all active roles
        $roles = Role::where('is_active', true)->whereNull('company_id')->get();

        // View variables for improved template
        $viewData = [
            'companies' => $companies,
            'assignedCompanies' => $assignedCompanies,
            'users' => $users,
            'roles' => $roles,
            'pageTitle' => 'Create Company Admin',
            'formAction' => route('superadmin.assign-company-admin.store'),
            'isEditMode' => false
        ];

        return view('superadmin.assign_company_admin', $viewData);
    }

    public function store(Request $request)
    {
        // Enhanced validation rules
        $validator = $this->validateStoreRequest($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->all());
        }

        // Get validated data
        $validated = $validator->validated();

        // Prevent duplicate company admin assignment
        $alreadyAssigned = Employee::where('company_id', $validated['company_id'])
            ->whereHas('user', function($q) {
                $q->where('role', 'company_admin');
            })
            ->exists();

        if ($alreadyAssigned) {
            return back()
                ->withErrors(['company_id' => 'This company already has a company admin assigned.'])
                ->withInput();
        }

        return $this->createCompanyAdmin($validated);
    }

    public function edit($id)
    {
        try {
            $admin = Employee::with(['user', 'company'])->findOrFail($id);

            // Pre-load companies with comprehensive data for dynamic display
            $companies = Company::with([
                'employees.user', // Load employees with their user relationships
                'users',
                'departments',
                'designations',
                'documents'
            ])->get();

            // Get assigned company IDs (excluding current admin's company)
            $assignedCompanies = $companies->filter(function($company) use ($admin) {
                $adminCount = $company->employees->filter(function($employee) use ($admin) {
                    return $employee->user &&
                           $employee->user->role === 'company_admin' &&
                           $employee->id !== $admin->id;
                })->count();
                return $adminCount > 0;
            })->pluck('id')->toArray();

            $users = User::where('role', 'user')
                ->orWhere('id', $admin->user_id)
                ->get();

            // Get all active roles
            $roles = Role::where('is_active', true)->whereNull('company_id')->get();

            $viewData = [
                'admin' => $admin,
                'companies' => $companies,
                'assignedCompanies' => $assignedCompanies,
                'users' => $users,
                'roles' => $roles,
                'pageTitle' => 'Edit Company Admin',
                'formAction' => route('superadmin.assign-company-admin.update', $admin->id),
                'isEditMode' => true
            ];

            return view('superadmin.assign_company_admin', $viewData);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Company admin not found.']);
        }
    }

    // public function update(Request $request, $id)
    // {
    //     try {
    //         $admin = Employee::with(['user'])->findOrFail($id);
    //         $user = $admin->user;

    //         // Enhanced validation rules
    //         $validator = $this->validateUpdateRequest($request, $user->id);

    //         if ($validator->fails()) {
    //             return back()
    //                 ->withErrors($validator)
    //                 ->withInput($request->all());
    //         }

    //         // Get validated data
    //         $validated = $validator->validated();

    //         // Prevent duplicate company admin assignment (except current record)
    //         $alreadyAssigned = Employee::where('company_id', $validated['company_id'])
    //             ->whereHas('user', function($q) {
    //                 $q->where('role', 'company_admin');
    //             })
    //             ->where('id', '!=', $admin->id)
    //             ->exists();

    //         if ($alreadyAssigned) {
    //             return back()
    //                 ->withErrors(['company_id' => 'This company already has a company admin assigned.'])
    //                 ->withInput();
    //         }

    //         return $this->updateCompanyAdmin($request, $admin, $user, $validated);

    //     } catch (\Exception $e) {
    //         return back()->withErrors(['error' => 'Company admin not found or update failed.']);
    //     }
    // }

    public function destroy($id)
    {
        $admin = Employee::findOrFail($id);
        DB::beginTransaction();

        try {
            $user = $admin->user;
            $user->role = 'employee';
            $user->removeRole();
            $user->save();

            $admin->delete();

            DB::commit();

            return redirect()
                ->route('superadmin.assigned-company-admins.index')
                ->with('success', 'Company admin assignment removed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to remove company admin assignment.']);
        }
    }

    /**
     * Enhanced validation for store request
     */
    private function validateStoreRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'role_id' => 'required|exists:roles,id',
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                'unique:users,email',
                'max:255'
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/'
            ],
            'dob' => [
                'required',
                'date',
                'before:' . now()->subYears(18)->format('Y-m-d'),
                'after:1940-01-01'
            ],
            'gender' => [
                'required',
                Rule::in(['male', 'female', 'other'])
            ],
            'emergency_contact' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/'
            ],
            'address' => [
                'required',
                'string',
                'max:500'
            ],
        ], [
            'name.regex' => 'Name should only contain letters and spaces.',
            'phone.regex' => 'Phone number must be exactly 10 digits.',
            'emergency_contact.regex' => 'Emergency contact must be exactly 10 digits.',
            'dob.before' => 'Company admin must be at least 18 years old.',
            'dob.after' => 'Please provide a valid date of birth.',
            'role_id.required' => 'Please select a role for the company admin.',
            'role_id.exists' => 'Selected role does not exist.',
        ]);
    }

    /**
     * Enhanced validation for update request
     */
    private function validateUpdateRequest(Request $request, $userId)
    {
        return Validator::make($request->all(), [
            'company_id' => 'required|exists:companies,id',
            'role_id' => 'required|exists:roles,id',
            'user_id' => 'required|exists:users,id',
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z\s]+$/'
            ],
            'email' => [
                'required',
                'email:rfc,dns',
                Rule::unique('users', 'email')->ignore($userId),
                'max:255'
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/'
            ],
            'dob' => [
                'required',
                'date',
                'before:' . now()->subYears(18)->format('Y-m-d'),
                'after:1940-01-01'
            ],
            'gender' => [
                'required',
                Rule::in(['male', 'female', 'other'])
            ],
            'emergency_contact' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/'
            ],
            'address' => [
                'required',
                'string',
                'max:500'
            ],
        ], [
            'name.regex' => 'Name should only contain letters and spaces.',
            'phone.regex' => 'Phone number must be exactly 10 digits.',
            'emergency_contact.regex' => 'Emergency contact must be exactly 10 digits.',
            'dob.before' => 'Company admin must be at least 18 years old.',
            'dob.after' => 'Please provide a valid date of birth.',
            'role_id.required' => 'Please select a role for the company admin.',
            'role_id.exists' => 'Selected role does not exist.',
            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'Selected user does not exist.',
        ]);
    }

    /**
     * Create company admin with enhanced error handling
     */
    private function createCompanyAdmin($validated)
    {
        // Generate secure password
        $password = config('app.env') === 'local'
            ? '12345678'
            : \Illuminate\Support\Str::random(12);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($password),
                'role' => 'company_admin',
                'role_id' => $validated['role_id'],
                'company_id' => $validated['company_id'],
            ]);

            // $user->assignRole('Company Admin');
            Log::info('AssignCompanyAdminController@store: User created', [
                'user_id' => $user->id,
                'role' => $user->role,
                'role_id' => $user->role_id,
                'company_id' => $user->company_id
            ]);

            // Ensure department exists
            $department = \App\Models\Department::firstOrCreate(
                ['name' => 'Company Admin', 'company_id' => $validated['company_id']],
                ['description' => 'Company Admin Department']
            );

            // Ensure designation exists
            $designation = \App\Models\Designation::firstOrCreate(
                ['title' => 'Company Admin', 'company_id' => $validated['company_id']],
                ['description' => 'Company Admin Designation', 'level' => 'Admin']
            );

            $employee = Employee::create([
                'user_id' => $user->id,
                'company_id' => $validated['company_id'],
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
            ]);

            // Send notification based on environment
            if (config('app.env') === 'local') {
                Log::info("Company Admin created with email: " . $user->email . " and password: " . $password);
            } else {
                $user->notify(new \App\Notifications\CompanyAdminWelcomeNotification($password));
            }

            DB::commit();

            return redirect()
                ->route('superadmin.assigned-company-admins.index')
                ->with('success', 'Company admin created successfully. Login credentials have been sent to the email address.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to assign company admin: ' . $e->getMessage()])->withInput();
        }
    }

    // public function edit($id)
    // {
    //     $admin = Employee::with(['user', 'company'])->findOrFail($id);
    //     $users = User::where('role', 'user')->orWhere('id', $admin->user_id)->get();
    //     $companies = Company::all();
    //     return view('superadmin.assign_company_admin', compact('admin', 'users', 'companies'));
    // }

    public function update(Request $request, $id)
    {
        $admin = Employee::findOrFail($id);
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->user_id,
            'phone' => 'nullable|string|max:10',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'emergency_contact' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'role_id'=>'nullable|exists:roles,id'
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
            $user = $admin->user;
            Log::info('AssignCompanyAdminController@update: User found', ['user_id' => $user->id, 'role' => $user->role]);

            // Update user information
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = 'company_admin';
            $user->company_id = $validated['company_id']; // Store company_id in user
            $user->role_id = $validated['role_id'] ?? null;
            $user->save();
            Log::info('AssignCompanyAdminController@update: User updated', ['user_id' => $user->id, 'role' => $user->role, 'company_id' => $user->company_id]);

            // Ensure department and designation exist
            $department = \App\Models\Department::firstOrCreate(
                ['name' => 'Company Admin', 'company_id' => $validated['company_id']],
                ['description' => 'Company Admin Department']
            );

            $designation = \App\Models\Designation::firstOrCreate(
                ['title' => 'Company Admin', 'company_id' => $validated['company_id']],
                ['description' => 'Company Admin Designation', 'level' => 1]
            );

            // Update employee data
            $admin->update([
                'user_id' => $user->id,
                'company_id' => $validated['company_id'],
                'department_id' => $department->id,
                'designation_id' => $designation->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $validated['phone'] ?? null,
                'dob' => $validated['dob'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
                'current_address' => $validated['address'] ?? null,
                'role_id' => $validated['role_id'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('superadmin.assigned-company-admins.index')
                ->with('success', 'Company admin updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AssignCompanyAdminController@update: Failed', ['error' => $e->getMessage()]);
            return back()
                ->withErrors(['error' => 'Failed to update company admin: ' . $e->getMessage()])
                ->withInput();
        }
    }
}
