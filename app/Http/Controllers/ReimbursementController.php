<?php

namespace App\Http\Controllers;

use App\Models\Reimbursement;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\TeamMember;
use Illuminate\Http\Request;


class ReimbursementController extends Controller
{
    public function index(Request $request)
{
    try {
        $user = Auth::user();

        // Start building the query with relationships
        $query = Reimbursement::with([
            'employee',
            'company',
            'reporter',
            'admin',
            'employee.user'
        ]);

        // Treat both admin and company_admin as privileged roles
        $isPrivileged = in_array($user->role, ['admin', 'company_admin']);

        if (!$isPrivileged) {
            // For regular users, only show their own reimbursements
            $employee = Employee::where('user_id', $user->id)->first();

            if ($employee) {
                $query->where('employee_id', $employee->id);
            } else {
                // No employee record means no reimbursements to show
                return view('reimbursements.index', ['reimbursements' => collect()]);
            }
        } else {
            // For admin/company_admin, only show reimbursements from their company
            if ($user->company_id) {
                $query->where('company_id', $user->company_id);
            } else {
                return view('reimbursements.index', ['reimbursements' => collect()])
                    ->with('warning', 'Your account is not associated with any company.');
            }
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Order by created date (newest first)
        $query->orderBy('created_at', 'desc');

        // Get paginated results
        $reimbursements = $query->paginate(15)->withQueryString();

        return view('reimbursements.index', compact('reimbursements'));

    } catch (\Exception $e) {
        Log::error('Error fetching reimbursements: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Error loading reimbursements: ' . $e->getMessage());
    }
}


    public function create()
    {
        return view('reimbursements.create');
    }

    public function pending()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        // Get pending and reporter-approved reimbursements for this employee
        $reimbursements = Reimbursement::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'reporter_approved'])
            ->with(['employee', 'company'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get pending reimbursements for which this employee is reporter
        $reporterReimbursements = Reimbursement::where('reporter_id', $employee->id)
            ->whereIn('status', ['pending', 'reporter_approved'])
            ->with(['employee', 'company'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('reimbursements.pending', compact('reimbursements', 'reporterReimbursements'));
    }

    public function approve(Request $request, Reimbursement $reimbursement)
    {
        try {
            Log::info('Starting reimbursement approval', [
                'reimbursement_id' => $reimbursement->id,
                'user_id' => Auth::id(),
                'payload' => $request->all()
            ]);

            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                $error = 'Please login to continue.';
                Log::warning('No authenticated user found during approval');
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $error
                    ], 401);
                }
                return redirect()->route('login')->with('error', $error);
            }

            // Get employee record with fallback to direct query
            $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                $error = 'Your employee record was not found. Please contact HR.';
                Log::error('Employee record not found for user', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $error
                    ], 404);
                }
                return redirect()->back()->with('error', $error);
            }

            // Verify company ownership
            if ($reimbursement->company_id !== $employee->company_id) {
                Log::warning('Company ownership verification failed', [
                    'user_company' => $employee->company_id,
                    'reimbursement_company' => $reimbursement->company_id
                ]);
                return redirect()->back()->with('error', 'This reimbursement does not belong to your company.');
            }

            // Check user role from the role column
            $isAdmin = $user->role === 'admin';
            $isCompanyAdmin = $user->role === 'company_admin';
            
            // Safely check for company admin role
            if (method_exists($user, 'hasRole')) {
                $isCompanyAdmin = $user->hasRole('company_admin');
            } else {
                $isCompanyAdmin = $user->role === 'company_admin';
            }
            
            Log::debug('User role check', [
                'user_id' => $user->id, 
                'role' => $user->role,
                'is_admin' => $isAdmin,
                'is_company_admin' => $isCompanyAdmin,
                'has_role_method' => method_exists($user, 'hasRole') ? 'exists' : 'missing'
            ]);
            
            // Check if user is the reporter
            $isReporter = $reimbursement->reporter_id === $employee->id;
            Log::debug('User permissions', [
                'user_id' => $user->id,
                'is_admin' => $isAdmin,
                'is_company_admin' => $isCompanyAdmin,
                'is_reporter' => $isReporter,
                'reimbursement_status' => $reimbursement->status
            ]);

            // Validate the correct status based on who is approving
            // if ($isAdmin) {
            //     if ($reimbursement->status !== 'reporter_approved') {
            //         \Log::warning('Admin tried to approve non-reporter-approved reimbursement', [
            //             'status' => $reimbursement->status
            //         ]);
            //         return redirect()->back()->with('error', 'This reimbursement must be approved by a reporter first.');
            //     }
            // } else if ($isReporter) {
            //     if ($reimbursement->status !== 'pending') {
            //         \Log::warning('Reporter tried to approve already processed reimbursement', [
            //             'status' => $reimbursement->status
            //         ]);
            //         return redirect()->back()->with('error', 'This reimbursement has already been processed.');
            //     }
            // } else {
            //     \Log::warning('Unauthorized approval attempt', [
            //         'user_id' => $user->id,
            //         'reimbursement_id' => $reimbursement->id
            //     ]);
            //     return redirect()->back()->with('error', 'You do not have permission to approve this reimbursement.');
            // }

            // Validate input
            $validated = $request->validate([
                'remarks' => 'required|string|max:1000',
            ]);

            // Update reimbursement with approval details
            if ($isAdmin || $isCompanyAdmin) {
                // $teamMember = DB::table('team_members')
                //     ->where('employee_id', $employee->id)
                //     ->first();
                
                // if (!$teamMember) {
                //     // If no team member record exists, create one for company admins
                //     if ($isCompanyAdmin) {
                //         $teamMember = (object)['employee_id' => $employee->id];
                //     } else {
                //         Log::error('Employee not found in team_members table', [
                //             'employee_id' => $employee->id,
                //             'user_id' => $user->id
                //         ]);
                //         return redirect()->back()->with('error', 'Your employee record is not properly set up in the system.');
                //     }
                // }
                
                $reimbursement->update([
                    'status' => 'admin_approved',
                    'admin_remarks' => $validated['remarks'],
                    'admin_approved_at' => now(),
                    
                ]);
                $message = 'Reimbursement approved by ' . ($isCompanyAdmin ? 'company admin' : 'admin') . ' successfully.';
            } else if ($isReporter) {
                $reimbursement->update([
                    'status' => 'reporter_approved',
                    'reporter_remarks' => $validated['remarks'],
                    'reporter_approved_at' => now(),
                    'reporter_id' => $employee->id
                ]);
                $message = 'Reimbursement approved by reporter. Waiting for final approval.';
            } else {
                return redirect()->back()->with('error', 'You do not have permission to approve this reimbursement.');
            }

            // Log the action
            Log::info('Reimbursement approved', [
                'reimbursement_id' => $reimbursement->id,
                'approved_by' => $user->id,
                'role' => $isAdmin ? 'admin' : 'reporter',
                'status' => $reimbursement->status,
                'remarks' => $validated['remarks']
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('reimbursements.index')
                ]);
            }

            return redirect()->route('reimbursements.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Error approving reimbursement', [
                'reimbursement_id' => $reimbursement->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error approving reimbursement: ' . $e->getMessage());
        }
    }

    public function approveReporter(Request $request, Reimbursement $reimbursement)
    {
        try {
            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                $error = 'Please login to continue.';
                Log::warning('No authenticated user found during reporter approval');
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $error
                    ], 401);
                }
                return redirect()->route('login')->with('error', $error);
            }

            // Get employee record with fallback to direct query
            $employee = $user->employee ?? Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                $error = 'Your employee record was not found. Please contact HR.';
                Log::error('Employee record not found for user during reporter approval', [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]);
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $error
                    ], 404);
                }
                return redirect()->back()->with('error', $error);
            }

            // Verify company ownership
            if ($reimbursement->company_id !== $employee->company_id) {
                return redirect()->back()->with('error', 'This reimbursement does not belong to your company.');
            }

            // Check status
            if ($reimbursement->status !== 'pending') {
                return redirect()->back()->with('error', 'This reimbursement cannot be approved at this stage.');
            }

            // Check permissions
            if ($employee->id !== $reimbursement->reporter_id) {
                return redirect()->back()->with('error', 'You do not have permission to approve this reimbursement.');
            }

            // Validate input
            $validated = $request->validate([
                'remarks' => 'required|string|max:1000',
            ]);

            // Update reimbursement with approval details
            $reimbursement->update([
                'status' => 'reporter_approved',
                'reporter_remarks' => $validated['remarks'],
                'reporter_approved_at' => now(),
                'reporter_id' => $user->id
            ]);

            // Log the action
            Log::info('Reimbursement reporter approved', [
                'reimbursement_id' => $reimbursement->id,
                'employee_id' => $employee->id,
                'status' => $reimbursement->status,
                'remarks' => $validated['remarks']
            ]);

            return redirect()->back()->with('success', 'Reimbursement approved by reporter. Awaiting final approval.');

        } catch (\Exception $e) {
            Log::error('Error approving reimbursement', [
                'reimbursement_id' => $reimbursement->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Error approving reimbursement: ' . $e->getMessage());
        }
    }



    public function store(Request $request)
    {
        try {
            Log::info('Starting reimbursement store process');
            Log::info('Request data: ' . json_encode($request->all()));

            // Get authenticated user
            $user = Auth::user();
            Log::info('Authenticated user: ' . $user->id . ' - ' . $user->email);
            
            // Validate input data
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'amount' => 'required',
                'expense_date' => 'required|date',
                'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            ]);
            Log::info('Validation successful');

            // Get employee record from database
            $employee = Employee::where('user_id', $user->id)->first();
            
            if (!$employee) {
                Log::error('No employee record found for user: ' . $user->id);
                Log::info('Creating new employee record for user: ' . $user->id);
                // $employee = Employee::create([
                //     'user_id' => $user->id,
                //     'company_id' => 1, // Default company ID, should be set properly
                //     'status' => 'active'
                // ]);
                Log::info('New employee record created: ' . $employee->id);
            }

            $companyId = $employee->company_id;
            Log::info('Employee ID: ' . $employee->id . ', Company ID: ' . $companyId);

            if (!$companyId) {
                Log::error('No company ID found for employee: ' . $employee->id);
                return redirect()->back()->with('error', 'Company ID not found. Please contact support.');
            }

            // Handle receipt file upload
            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                try {
                    $file = $request->file('receipt');
                    $extension = $file->getClientOriginalExtension();
                    $employeeName = str_replace(' ', '-', strtolower($employee->name));
                    $fileName = $employeeName . '-reimbursement-receipt-' . now()->format('YmdHis') . '.' . $extension;
                    $receiptPath = $file->storeAs('receipts', $fileName, 'public');
                } catch (\Exception $e) {
                    Log::error('Error uploading receipt: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Error uploading receipt file. Please try again.');
                }
            }

            // Get reporter ID
            $reporter_id = null;
            Log::info('Looking for reporter for employee: ' . $employee->id);
            $reporter = TeamMember::where('employee_id', $employee->id)->first();
            if ($reporter) {
                $reporter_id = $reporter->reporter_id;
                Log::info('Found reporter: ' . $reporter_id);
            } else {
                Log::info('No reporter found for employee');
            }

            // Create reimbursement with all required fields
            Log::info('Creating reimbursement record');
            $reimbursement = Reimbursement::create([
                'employee_id' => $employee->id,
                'company_id' => $companyId,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'amount' => $validated['amount'],
                'expense_date' => $validated['expense_date'],
                'receipt_path' => $receiptPath,
                'status' => 'pending',
                'reporter_remarks' => null,
                'admin_remarks' => null,
                'reporter_id' => $reporter_id,
                'admin_id' => null,
                'reporter_approved_at' => null,
                'admin_approved_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            Log::info('Reimbursement created successfully: ' . $reimbursement->id);

            return redirect()->route('reimbursements.index')->with('success', 'Reimbursement request submitted successfully.');

        } catch (\Exception $e) {
            Log::error('Exception in reimbursement store: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Error creating reimbursement: ' . $e->getMessage());
        }
    }

    public function show(Reimbursement $reimbursement)
    { 
        // No auth check, everyone can view any reimbursement
        return view('reimbursements.show', compact('reimbursement'));
    }

    /**
     * Reject a reimbursement request
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Reimbursement  $reimbursement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, Reimbursement $reimbursement)
    {
        try {
            Log::info('Starting reimbursement rejection', [
                'reimbursement_id' => $reimbursement->id,
                'user_id' => Auth::id(),
                'payload' => $request->all()
            ]);

            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                $error = 'Please login to continue.';
                Log::warning('Unauthenticated rejection attempt');
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $error
                    ], 401);
                }
                return redirect()->route('login')->with('error', $error);
            }
    
            // Get employee record with relationships
            try {
                $employee = Employee::with(['user', 'department', 'designation'])
                    ->where('user_id', $user->id)
                    ->first();
                
                if (!$employee) {
                    // If no employee record exists, create a basic one
                    $employee = new Employee([
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'department_id' => 1, // Default department
                        'designation_id' => 1, // Default designation
                        'joining_date' => now(),
                        'status' => 'active',
                        'created_by' => $user->id
                    ]);
                    
                    if (!$employee->save()) {
                        throw new \Exception('Failed to create employee record');
                    }
                    
                    Log::info('Created new employee record for user', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'employee_id' => $employee->id
                    ]);
                }
            } catch (\Exception $e) {
                $error = 'Failed to process employee record. Please contact HR.';
                Log::error('Error accessing employee record', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage()
                ]);
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $error,
                        'error_details' => config('app.debug') ? $e->getMessage() : null
                    ], 500);
                }
                return redirect()->back()->with('error', $error);
            }
    
            // Verify company ownership
            if ($reimbursement->company_id !== $employee->company_id) {
                Log::warning('Company ownership verification failed', [
                    'user_company' => $employee->company_id,
                    'reimbursement_company' => $reimbursement->company_id
                ]);
                return redirect()->back()->with('error', 'This reimbursement does not belong to your company.');
            }
    
            // Check status
            if (!in_array($reimbursement->status, ['pending', 'reporter_approved'])) {
                Log::warning('Invalid status for rejection', [
                    'current_status' => $reimbursement->status,
                    'allowed_statuses' => ['pending', 'reporter_approved']
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'This reimbursement cannot be rejected at this stage.'
                ], 400);
            }
    
            // Check user role from the role column
            $isAdmin = $user->role === 'admin';
            $isCompanyAdmin = $user->role === 'company_admin';
            $isPrivileged = $isAdmin || $isCompanyAdmin;
            
            Log::debug('User role check', [
                'user_id' => $user->id, 
                'role' => $user->role,
                'is_admin' => $isAdmin,
                'is_company_admin' => $isCompanyAdmin
            ]);
            
            // Check if user is the reporter
            $isReporter = $reimbursement->reporter_id === $employee->id;
            Log::debug('User permissions', [
                'user_id' => $user->id,
                'is_privileged' => $isPrivileged,
                'is_reporter' => $isReporter,
                'reimbursement_status' => $reimbursement->status
            ]);
            
            if (!$isPrivileged && !$isReporter) {
                Log::warning('Unauthorized rejection attempt', [
                    'user_id' => $user->id,
                    'reimbursement_id' => $reimbursement->id,
                    'is_privileged' => $isPrivileged,
                    'is_reporter' => $isReporter
                ]);
                return redirect()->back()->with('error', 'You do not have permission to reject this reimbursement.');
            }
    
            // Validate input
            $validated = $request->validate([
                'remarks' => 'required|string|max:1000',
            ]);
            
            // Check if employee exists in team_members table for admin actions
            $teamMember = null;
            if ($isAdmin) {
                $teamMember = DB::table('team_members')
                    ->where('employee_id', $employee->id)
                    ->first();
                
            //     if (!$teamMember) {
            //         Log::error('Employee not found in team_members table', [
            //             'employee_id' => $employee->id,
            //             'user_id' => $user->id
            //         ]);
            //         return redirect()->back()->with('error', 'Your employee record is not properly set up in the system.');
            //     }
            }
            
            // Start database transaction
            DB::beginTransaction();
            
            try {
                // Update reimbursement with rejection details
                $updateData = [
                    'status' => 'rejected',
                    'remarks' => $validated['remarks'],
                    'rejected_at' => now(),
                    'rejected_by' => $user->id,
                    // Clear approval timestamps if it was previously approved
                    'reporter_approved_at' => null,
                    'admin_approved_at' => null
                ];
                
                // Only set admin_id if the user is an admin
                // if ($isAdmin) {
                //     $updateData['admin_id'] = $teamMember->employee_id;
                // }
                
                $reimbursement->update($updateData);
                
                // Commit the transaction
                DB::commit();
                
                // Log the action
                Log::info('Reimbursement rejected', [
                    'reimbursement_id' => $reimbursement->id,
                    'rejected_by' => $user->id,
                    'role' => $isAdmin ? 'admin' : 'reporter',
                    'status' => $reimbursement->status,
                    'remarks' => $validated['remarks'],
                    'team_member_id' => $teamMember ? $teamMember->id : null
                ]);
                
                return redirect()->route('reimbursements.show', $reimbursement->id)
                    ->with('success', 'Reimbursement has been rejected successfully.');
                
            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error rejecting reimbursement', [
                'reimbursement_id' => $reimbursement->id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('reimbursements.show', $reimbursement->id)
                ->with('error', 'An error occurred while rejecting the reimbursement. Please try again.');
        }
    }
}
