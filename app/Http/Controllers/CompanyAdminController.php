<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\ModuleAccess;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\EmployeeIdPrefix;
use App\Models\EmploymentType;

class CompanyAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,company_admin');
    }

    /**
     * Display module access management page.
     */
    public function moduleAccess()
    {
        $user = Auth::user();
        $company = $user->employee->company;
        
        // Define all possible modules and roles
        $allModules = [
            'leave', 
            'reimbursement', 
            'team',
            'payroll',
            'attendance'
        ];
        $roles = ['admin', 'employee','company_admin'];
        
        // Initialize modules array with default values
        $modules = [];
        foreach ($allModules as $module) {
            $modules[$module] = [];
            foreach ($roles as $role) {
                $modules[$module][$role] = false;
            }
        }
        
        // Get current module access settings from database
        $moduleAccess = ModuleAccess::where('company_id', $company->id)
            ->get()
            ->groupBy('module_name');
            
        // Merge database values with default values
        foreach ($moduleAccess as $module => $accesses) {
            foreach ($accesses as $access) {
                if (isset($modules[$module][$access->role])) {
                    $modules[$module][$access->role] = (bool)$access->has_access;
                }
            }
        }

        return view('company-admin.module-access.index', compact('modules'));
    }

    /**
     * Update module access settings.
     */
    public function updateModuleAccess(Request $request)
    {
        try {
            $user = Auth::user();
            $company = $user->employee->company;

            DB::beginTransaction();

            // Define all possible module-role combinations
            $modules = [
                'leave', 
                'reimbursement', 
                'team', 
                'department',
                'payroll',
                'attendance',
                'recruitment',
                'performance',
                'documents',
                'reports',
                'settings'
            ];
            $roles = ['admin', 'employee', 'reporter'];
            
            // Process each module and role combination
            foreach ($modules as $module) {
                foreach ($roles as $role) {
                    // Check if this module-role combination was submitted in the form
                    $hasAccess = $request->has("modules.{$module}.{$role}");
                    
                    // Update or create the record
                    ModuleAccess::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'module_name' => $module,
                            'role' => $role,
                        ],
                        ['has_access' => $hasAccess]
                    );
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Module access settings updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating module access: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating module access settings: ' . $e->getMessage());
        }
    } // Added the missing closing brace here

    public function employees(Request $request)
    {
        $user = Auth::user();
        
        // Handle both admin and company_admin roles
        if ($user->role === 'admin') {
            // For admin, get company from user's company_id
            $companyId = $user->company_id;
            if (!$companyId) {
                abort(403, 'Admin user must be assigned to a company.');
            }
            // Get company object for admin
            $company = \App\Models\Company::find($companyId);
        } else {
            // For company_admin, get company from employee relationship
            $company = $user->employee->company;
            $companyId = $company->id;
        }

        // Get departments and designations for filters
        $departments = Department::where('company_id', $company->id)->get();
        $designations = Designation::where('company_id', $company->id)->get();

        // Build query with filters
        $query = Employee::with(['user', 'department', 'designation'])
            ->where('company_id', $companyId);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('employee_code', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', '%' . $search . '%')
                               ->orWhere('email', 'like', '%' . $search . '%');
                  });
            });
        }

        // Apply department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Apply designation filter
        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->designation_id);
        }

        $employees = $query->paginate(10)->appends($request->query());

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'html' => view('company-admin.employees._table', compact('employees'))->render(),
                'pagination' => $employees->links('pagination::bootstrap-5')->render()
            ]);
        }

        return view('company-admin.employees.index', compact('employees', 'departments', 'designations'));
    }

    /**
     * Update employee role.
     */
    public function updateEmployeeRole(Request $request, Employee $employee)
    {
        try {
            $user = Auth::user();
            $company = $user->employee->company;

            // Ensure employee belongs to the same company
            if ($employee->company_id !== $company->id) {
                return redirect()->back()->with('error', 'Unauthorized action.');
            }

            $request->validate([
                'role' => 'required|in:admin,employee,reporter'
            ]);

            DB::beginTransaction();

            // Load the user relationship if not already loaded
            $employee->load('user');
            
            // Check if user exists
            if (!$employee->user) {
                throw new \Exception('User record not found for this employee.');
            }

            // Prevent changing company_admin role
            if ($employee->user->role === 'company_admin' || $request->role === 'company_admin') {
                return redirect()->back()->with('error', 'Changing the company_admin role is not allowed.');
            }

            // Update user role
            $employee->user->role = $request->role;
            $employee->user->save();

            DB::commit();
            return redirect()->back()->with('success', 'Employee role updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee role: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating employee role: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new employee.
     */
    public function createEmployee()
    {
        $user = Auth::user();
        $company = $user->employee->company;

        $departments = \App\Models\Department::where('company_id', $company->id)->get();
        $designations = \App\Models\Designation::where('company_id', $company->id)->get();
        $managers = \App\Models\Employee::where('company_id', $company->id)->get();
        $employmentTypes = \App\Models\EmploymentType::forCompany($company->id)->active()->get();

        return view('company-admin.employees.create', compact('company', 'departments', 'designations', 'managers', 'employmentTypes'));
    }

    /**
     * Show the employee details view page.
     */
    public function viewEmployee($id)
    {
        $user = Auth::user();
        $company = $user->employee->company;

        $employee = \App\Models\Employee::with(['department', 'designation', 'reportingManager', 'currentSalary'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $departments = \App\Models\Department::where('company_id', $company->id)->get();
        $designations = \App\Models\Designation::where('company_id', $company->id)->get();
        $managers = \App\Models\Employee::where('company_id', $company->id)->get();
        $employmentTypes = \App\Models\EmploymentType::forCompany($company->id)->active()->get();

        // Fetch employee documents
        $documents = \App\Models\EmployeeDocument::where('employee_id', $employee->id)->get()->groupBy('type');

        return view('company-admin.employees.view', compact('employee', 'departments', 'designations', 'managers', 'documents'));
    }

    /**
     * Show the form for editing an employee.
     */
    // public function editEmployee($id)
    // {
    //     $user = Auth::user();
    //     $company = $user->employee->company;

    //     $employee = \App\Models\Employee::with(['currentSalary'])
    //         ->where('company_id', $company->id)
    //         ->findOrFail($id);

    //     $departments = \App\Models\Department::where('company_id', $company->id)->get();
    //     $designations = \App\Models\Designation::where('company_id', $company->id)->get();
    //     $managers = \App\Models\Employee::where('company_id', $company->id)->get();
    //     // Fetch employee documents
    //     $documents = \App\Models\EmployeeDocument::where('employee_id', $employee->id)->get()->groupBy('type');

    //     return view('company-admin.employees.edit', compact('employee', 'departments', 'designations', 'managers', 'company', 'documents'));
    // }



    public function editEmployee($id)
    {
        $user = Auth::user();
        $company = $user->employee->company;

        $employee = \App\Models\Employee::with(['currentSalary'])
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $departments = \App\Models\Department::where('company_id', $company->id)->get();

        // Fetch only designations related to employee's department for edit preselect
        $designations = $employee->department_id
            ? \App\Models\Designation::where('company_id', $company->id)
                ->where('department_id', $employee->department_id)
                ->get()
            : collect(); // empty collection if department not set

        $managers = \App\Models\Employee::where('company_id', $company->id)->get();

        // Fetch employee documents
        $documents = \App\Models\EmployeeDocument::where('employee_id', $employee->id)
            ->get()
            ->groupBy('type');

        $employmentTypes = \App\Models\EmploymentType::forCompany($company->id)->active()->get();

        return view('company-admin.employees.edit', compact(
            'employee', 'departments', 'designations', 'managers', 'company', 'documents', 'employmentTypes'
        ));
    }


    /**
     * Update the specified employee in storage.
     */
    public function updateEmployee(Request $request, $id)
    {
        $user = Auth::user();
        $company = $user->employee->company;

        $employee = \App\Models\Employee::where('company_id', $company->id)->findOrFail($id);

        $validated = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'parent_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date|before:18 years ago',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'contact_number' => 'required|digits:10|unique:employees,phone,' . $employee->id,
            'personal_email' => 'required|email|unique:employees,email,' . $employee->id,
            'official_email' => 'nullable|email|unique:employees,official_email,' . $employee->id,
            'current_address' => 'required|string',
            'permanent_address' => 'required|string',
            // Job Details
            'employee_code' => 'required|string|unique:employees,employee_code,' . $employee->id, // readonly, generated
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'employment_type_id' => 'required|exists:employment_types,id',
            'joining_date' => 'required|date',
            'location' => 'required|string',
            'probation_period' => 'nullable|integer',
            'reporting_manager' => 'required|exists:employees,id',
            // Salary Details
            'ctc' => 'required|numeric|min:0|gte:basic_salary',
            'basic_salary' => 'required|numeric|min:0|lte:ctc',
            'bank_name' => 'required|string',
            'account_number' => 'required|numeric|unique:employee_salaries,account_number,' . ($employee->currentSalary->id ?? 'NULL'),
            'ifsc_code' => 'required|string',
            'pan_number' => 'required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/|unique:employee_salaries,pan_number,' . ($employee->currentSalary->id ?? 'NULL'),
            // Other Details
            'emergency_contact' => 'required|numeric|digits:10',
            'emergency_contact_relation' => 'required|string',
            'emergency_contact_name' => 'required|string',
            'blood_group' => 'nullable|string',
            'nominee_details' => 'nullable|string',
            // Documents
            'aadhaar_card.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'pan_card.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'passport_photo.*' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'resume.*' => 'nullable|file|mimes:pdf|max:2048',
            'qualification_certificate.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'experience_letters.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'relieving_letter.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'offer_letter.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'bank_passbook.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'signed_offer_letter.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        try {
            \DB::beginTransaction();

            // Update employee record
            $employee->update([
                'employee_code' => $this->generateEmployeeCode($company, $validated['employment_type_id']),
                'name' => $validated['name'],
                'parent_name' => $validated['parent_name'],
                'gender' => $validated['gender'],
                'dob' => $validated['dob'],
                'marital_status' => $validated['marital_status'],
                'contact_number' => $validated['contact_number'],
                'email' => $validated['personal_email'],
                'official_email' => $validated['official_email'],
                'current_address' => $validated['current_address'],
                'permanent_address' => $validated['permanent_address'],
                'department_id' => $validated['department_id'],
                'designation_id' => $validated['designation_id'],
                'employment_type_id' => $validated['employment_type_id'],
                'joining_date' => $validated['joining_date'],
                'location' => $validated['location'],
                'probation_period' => $validated['probation_period'],
                'reporting_manager_id' => $validated['reporting_manager'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_contact_relation' => $validated['emergency_contact_relation'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'blood_group' => $validated['blood_group'],
                'nominee_details' => $validated['nominee_details'],
                'created_by' => $user->id,
            ]);

            // Handle document uploads
            $documentTypes = [
                'aadhaar_card', 'pan_card', 'passport_photo', 'resume',
                'qualification_certificate', 'experience_letters', 'relieving_letter',
                'offer_letter', 'bank_passbook', 'signed_offer_letter'
            ];

            foreach ($documentTypes as $docType) {
                $paths = [];
                if ($request->hasFile($docType)) {
                    foreach ($request->file($docType) as $file) {
                        $paths[] = $file->store('employee_documents/' . $employee->id, 'public');
                    }
                }
                if (count($paths)) {
                    \App\Models\EmployeeDocument::create([
                        'company_id' => $company->id,
                        'employee_id' => $employee->id,
                        'type' => $docType,
                        'file_path' => json_encode($paths),
                    ]);
                }
            }

            // Update salary record
            $salary = $employee->currentSalary;
            if ($salary) {
                $salary->update([
                    'ctc' => $validated['ctc'],
                    'basic_salary' => $validated['basic_salary'],
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'ifsc_code' => $validated['ifsc_code'],
                    'pan_number' => $validated['pan_number'],
                ]);
            } else {
                // Create salary record if not exists
                \App\Models\EmployeeSalary::create([
                    'employee_id' => $employee->id,
                    'ctc' => $validated['ctc'],
                    'basic_salary' => $validated['basic_salary'],
                    'bank_name' => $validated['bank_name'],
                    'account_number' => $validated['account_number'],
                    'ifsc_code' => $validated['ifsc_code'],
                    'pan_number' => $validated['pan_number'],
                    'status' => 'active',
                    'currency' => $employee->company->default_currency ?? config('app.currency', 'INR'),
                    'payment_frequency' => 'monthly',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'effective_from' => now(),
                    'is_current' => true,
                ]);
            }

            \DB::commit();

            return redirect()->route('company-admin.employees.index')->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating employee: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update employee: ' . $e->getMessage())->withInput();
        }
    }

    private function generateEmployeeCode($company, $employmentTypeId = null)
    {
        // Get the prefix settings for the company
        $prefixSettings = EmployeeIdPrefix::with('employmentType')
            ->where('company_id', $company->id)
            ->get();

        // If no prefix settings found, use default format
        if ($prefixSettings->isEmpty()) {
            $prefix = '#' . strtoupper(substr($company->name, 0, 3));

            // Get the highest existing employee code with this prefix
            $lastEmployee = Employee::where('company_id', $company->id)
                ->whereNotNull('employee_code')
                ->where('employee_code', 'like', $prefix . '%')
                ->orderByRaw('LENGTH(employee_code) DESC, employee_code DESC')
                ->first();

            $nextNumber = 1;
            if ($lastEmployee) {
                // Extract the numeric part from the code
                preg_match('/\d+$/', $lastEmployee->employee_code, $matches);
                $numericPart = $matches[0] ?? '0';
                $nextNumber = intval($numericPart) + 1;
            }

            // Generate the new code and ensure it's unique
            do {
                $newCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                $exists = Employee::where('employee_code', $newCode)->exists();
                if (!$exists) {
                    return $newCode;
                }
                $nextNumber++;
            } while (true);
        }

        // Check for common prefix settings
        $commonPrefix = $prefixSettings->where('is_common', true)->first();
        if ($commonPrefix) {
            // Use common settings for all employment types
            $prefixSetting = $commonPrefix;
        } else {
            // Look for type-specific settings
            if ($employmentTypeId) {
                // Find the employment type by ID
                $prefixSetting = $prefixSettings->where('employment_type_id', $employmentTypeId)->first();
            }

            // If no specific setting found, use the first available setting or default
            if (!isset($prefixSetting) || !$prefixSetting) {
                $prefixSetting = $prefixSettings->first();
            }
        }

        // If we still don't have a prefix setting, use default
        if (!isset($prefixSetting) || !$prefixSetting) {
            $prefix = '#' . strtoupper(substr($company->name, 0, 3));

            // Get the highest existing employee code with this prefix
            $lastEmployee = Employee::where('company_id', $company->id)
                ->whereNotNull('employee_code')
                ->where('employee_code', 'like', $prefix . '%')
                ->orderByRaw('LENGTH(employee_code) DESC, employee_code DESC')
                ->first();

            $nextNumber = 1;
            if ($lastEmployee) {
                // Extract the numeric part from the code
                preg_match('/\d+$/', $lastEmployee->employee_code, $matches);
                $numericPart = $matches[0] ?? '0';
                $nextNumber = intval($numericPart) + 1;
            }

            // Generate the new code and ensure it's unique
            do {
                $newCode = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                $exists = Employee::where('employee_code', $newCode)->exists();
                if (!$exists) {
                    return $newCode;
                }
                $nextNumber++;
            } while (true);
        }

        // Generate the new code based on prefix settings (PERMANENTLY APPLIED)
        $maxAttempts = 100; // Prevent infinite loops
        $attempt = 0;

        do {
            // Get the last employee number for this prefix
            $lastEmployee = Employee::where('company_id', $company->id)
                ->where('employee_code', 'LIKE', $prefixSetting->prefix . '%')
                ->orderByRaw('LENGTH(employee_code) DESC, employee_code DESC')
                ->first();

            $nextNumber = $prefixSetting->start;
            if ($lastEmployee) {
                // Extract the number from the last employee code
                $lastNumber = intval(substr($lastEmployee->employee_code, strlen($prefixSetting->prefix)));
                $nextNumber = $lastNumber + 1;
            }

            // Format the number according to padding settings
            $formattedNumber = str_pad($nextNumber, $prefixSetting->padding, '0', STR_PAD_LEFT);
            $newCode = $prefixSetting->prefix . $formattedNumber;

            // Check if the code already exists
            $exists = Employee::where('employee_code', $newCode)->exists();
            if (!$exists) {
                return $newCode;
            }

            $attempt++;
            if ($attempt >= $maxAttempts) {
                throw new \Exception('Failed to generate a unique employee code after ' . $maxAttempts . ' attempts');
            }

            // If we get here, the code exists, so we'll try the next number
            $prefixSetting->start = $nextNumber + 1;

        } while (true);
    }

    public function storeEmployee(Request $request)
    {
        \Log::info('CompanyAdminController@storeEmployee called');
        $user = Auth::user();
        $company = $user->employee->company;
        // Validate the request
        $validated = $request->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'parent_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date|before:18 years ago',
            'marital_status' => 'required|in:single,married,divorced,widowed',
            'contact_number' => 'required|digits:10|unique:employees,phone',
            'personal_email' => 'required|email|unique:employees,email',
            'official_email' => 'nullable|email|unique:employees,official_email',
            'current_address' => 'required|string',
            'permanent_address' => 'required|string',
            // Job Details
            'employee_code' => 'required|string|unique:employees,employee_code', // readonly, generated
            'department_id' => 'required|exists:departments,id',
            'designation_id' => 'required|exists:designations,id',
            'employment_type_id' => 'required|exists:employment_types,id',
            'joining_date' => 'required|date',
            'location' => 'required|string',
            'probation_period' => 'nullable|integer',
            'reporting_manager' => 'required|exists:employees,id',
            // Salary Details
            'ctc' => 'required|numeric|min:0|gte:basic_salary',
            'basic_salary' => 'required|numeric|min:0|lte:ctc',
            'bank_name' => 'required|string',
            'account_number' => 'required|numeric|unique:employee_salaries,account_number',
            'ifsc_code' => 'required|string',
            'pan_number' => 'required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/|unique:employee_salaries,pan_number',
            // Other Details
            'emergency_contact' => 'required|numeric|digits:10',
            'emergency_contact_relation' => 'required|string',
            'emergency_contact_name' => 'required|string',
            'blood_group' => 'nullable|string',
            'nominee_details' => 'nullable|string',
            // Documents
            'aadhaar_card.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'pan_card.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'passport_photo.*' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'resume.*' => 'nullable|file|mimes:pdf|max:2048',
            'qualification_certificate.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'experience_letters.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'relieving_letter.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'offer_letter.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'bank_passbook.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
            'signed_offer_letter.*' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);
        \Log::info('Employee creation validation passed');

        try {
            DB::beginTransaction();
            \Log::info('Employee created successfully');
            // Generate a secure random password
            if(config('app.env') === 'local'){
                $password = '12345678'; // Fixed password for local environment
            } else {
                $password = \Illuminate\Support\Str::random(12);
            }

            // Create user account
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['personal_email'], // Use personal email for login
                'password' => Hash::make($password),
                'company_id' => $company->id,
                'status' => 'active',
                'role' => 'employee'
            ]);

            // Create employee record
            $employee = Employee::create([
                'user_id' => $user->id,
                'company_id' => $company->id,
                'employee_code' => $this->generateEmployeeCode($company, $validated['employment_type_id']),
                'name' => $validated['name'],
                'parent_name' => $validated['parent_name'],
                'gender' => $validated['gender'],
                'dob' => $validated['dob'],
                'marital_status' => $validated['marital_status'],
                'contact_number' => $validated['contact_number'],
                'email' => $validated['personal_email'],
                'official_email' => $validated['official_email'],
                'phone' => $validated['contact_number'],
                'department_id' => $validated['department_id'],
                'designation_id' => $validated['designation_id'],
                'employment_type_id' => $validated['employment_type_id'],
                'joining_date' => $validated['joining_date'],
                'location' => $validated['location'],
                'probation_period' => $validated['probation_period'],
                'reporting_manager_id' => $validated['reporting_manager'],
                'current_address' => $validated['current_address'],
                'permanent_address' => $validated['permanent_address'],
                'emergency_contact' => $validated['emergency_contact'],
                'emergency_contact_relation' => $validated['emergency_contact_relation'],
                'emergency_contact_name' => $validated['emergency_contact_name'],
                'blood_group' => $validated['blood_group'],
                'nominee_details' => $validated['nominee_details'],
                // 'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            // Ensure values are properly formatted as floats
            $ctc = (float) $validated['ctc'];
            $basicSalary = (float) $validated['basic_salary'];
            $companyCurrency = $employee->company->default_currency ?? config('app.currency', 'INR');
            
            // Calculate HRA (50% of basic) and DA (20% of basic)
            $hra = $basicSalary * 0.5;
            $da = $basicSalary * 0.2;
            $otherAllowances = max(0, $ctc - ($basicSalary + $hra + $da));
            
            // Calculate gross salary as sum of all components
            $grossSalary = $basicSalary + $hra + $da + $otherAllowances;
                        
            $salaryData = [
                'employee_id' => $employee->id,
                'ctc' => $ctc,
                'gross_salary' => $grossSalary,
                'net_salary' => $grossSalary,  // Net is same as gross before deductions
                'basic_salary' => $basicSalary,
                'hra' => $hra,
                'da' => $da,
                'other_allowances' => $otherAllowances,
                'status' => 'active',
                'currency' => $companyCurrency,
                'payment_frequency' => 'monthly',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'effective_from' => now(),
                'is_current' => true,
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'ifsc_code' => $validated['ifsc_code'],
                'pan_number' => $validated['pan_number'],
                // pan_number
            ];

            // Create salary record
            \App\Models\EmployeeSalary::create($salaryData);

            // \App\Models\EmployeeSalary::create([
            //     'employee_id' => $employee->id,
            //     'ctc' => $validated['ctc'],
            //     'basic_salary' => $validated['basic_salary'],
            //     'bank_name' => $validated['bank_name'],
            //     'account_number' => $validated['account_number'],
            //     'ifsc_code' => $validated['ifsc_code'],
            //     'pan_number' => $validated['pan_number'],
            //     // 'created_by' => Auth::id(),
            // ]);

            // Handle document uploads
            $documentTypes = [
                'aadhaar_card', 'pan_card', 'passport_photo', 'resume',
                'qualification_certificate', 'experience_letters', 'relieving_letter',
                'offer_letter', 'bank_passbook', 'signed_offer_letter'
            ];

            foreach ($documentTypes as $docType) {
                $paths = [];
                if ($request->hasFile($docType)) {
                    foreach ($request->file($docType) as $file) {
                        $paths[] = $file->store('employee_documents/' . $employee->id, 'public');
                    }
                }
                if (count($paths)) {
                    \App\Models\EmployeeDocument::create([
                        'company_id' => $company->id,
                        'employee_id' => $employee->id,
                        'type' => $docType,
                        'file_path' => json_encode($paths),
                    ]);
                }
            }

            DB::commit();

            // Send welcome email with credentials
            if(config('app.env') === 'local'){
                \Log::info("Employee created with email: " . $user->email . " and password: " . $password);
            } else {
                $user->notify(new \App\Notifications\EmployeeWelcomeNotification($password));
            }            

            return redirect()->route('company-admin.employees.index')
                ->with('success', 'Employee created successfully. Login credentials have been sent to the email address.');

        } catch (\Exception $e) {
            \Log::error('Error in CompanyAdminController@storeEmployee: ' . $e->getMessage());
            DB::rollBack();
            Log::error('Error in CompanyAdminController@storeEmployee: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display company settings.
     */
    public function settings()
    {
        $user = Auth::user();
        $company = $user->employee->company;

        // Get active employment types for the company
        $employmentTypes = EmploymentType::forCompany($company->id)->active()->get();

        // Get existing prefix settings
        $prefixes = \App\Models\EmployeeIdPrefix::with('employmentType')
            ->where('company_id', $company->id)
            ->get();

        $prefixData = [
            'status' => 'empty',
            'data' => null
        ];

        if ($prefixes->isNotEmpty()) {
            // Check for common prefix (is_common = true)
            $commonPrefix = $prefixes->where('is_common', true)->first();
            if ($commonPrefix) {
                $prefixData = [
                    'status' => 'common',
                    'data' => $commonPrefix
                ];
            } else {
                // Return type-specific prefixes
                $typeSpecificPrefixes = $prefixes->where('is_common', false)->filter(function($prefix) {
                    return $prefix->employment_type_id !== null;
                })->keyBy('employment_type_id');

                if ($typeSpecificPrefixes->isNotEmpty()) {
                    $prefixData = [
                        'status' => 'specific',
                        'data' => $typeSpecificPrefixes
                    ];
                }
            }
        }

        return view('company-admin.settings.index', compact('company', 'employmentTypes', 'prefixData'));
    }

    /**
     * Save employee ID prefix settings.
     */
    public function saveEmployeeIdPrefix(Request $request)
    {
        $user = $request->user();
        $company = $user->employee->company;
        Log::info('Request data: ' . json_encode($request->all()));

        // Validate common fields
        $request->validate([
            'prefix_mode' => 'required|string|in:same_for_all,type_specific',
        ]);

        $prefixMode = $request->prefix_mode;

        if ($prefixMode === 'same_for_all') {
            // Validate same_for_all fields
            $request->validate([
                'prefix' => 'required|string|max:255',
                'padding' => 'required|integer|min:1|max:6',
                'start' => 'required|integer|min:1',
            ]);

            // Save common settings for all employment types
            EmployeeIdPrefix::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'is_common' => true
                ],
                [
                    'prefix' => $request->prefix,
                    'padding' => $request->padding,
                    'start' => $request->start,
                    'employment_type_id' => null,
                    'employment_type' => null,
                ]
            );
        } else {
            // Validate type_specific fields
            $request->validate([
                'types' => 'required|array',
                'types.*.prefix' => 'required|string|max:255',
                'types.*.padding' => 'required|integer|min:1|max:6',
                'types.*.start' => 'required|integer|min:1',
            ]);

            // Get all employment types for the company
            $employmentTypes = EmploymentType::forCompany($company->id)->active()->get();

            // Save settings for each employment type
            foreach ($employmentTypes as $employmentType) {
                $typeId = $employmentType->id;
                if (isset($request->types[$typeId])) {
                    EmployeeIdPrefix::updateOrCreate(
                        [
                            'company_id' => $company->id,
                            'employment_type_id' => $typeId,
                            'is_common' => false
                        ],
                        [
                            'prefix' => $request->types[$typeId]['prefix'],
                            'padding' => $request->types[$typeId]['padding'],
                            'start' => $request->types[$typeId]['start'],
                            'employment_type' => $employmentType->name,
                        ]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Employee ID prefix settings saved successfully.');
    }

    /**
     * Update company settings.
     */
    public function updateSettings(Request $request)
    {
        try {
            $user = Auth::user();
            $company = $user->employee->company;

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'website' => 'required|url',
                'address' => 'required|string'
            ]);

            $company->update($request->all());

            return redirect()->back()->with('success', 'Company settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating company settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error updating company settings.');
        }
    }

}

