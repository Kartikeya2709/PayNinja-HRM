<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeResignation;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ResignationController extends Controller
{
    /**
     * Display a listing of the employee's resignation requests.
     */
    public function index()
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        
        $resignations = EmployeeResignation::where('employee_id', $employee->id)
            ->with(['reportingManager', 'hrAdmin', 'approver'])
            ->latest()
            ->get();
            
        return view('employee.resignations.index', compact('resignations'));
    }

    /**
     * Show the form for creating a new resignation request.
     */
    public function create()
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        
        // Check if employee has any active resignation
        $activeResignation = EmployeeResignation::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'hr_approved', 'manager_approved', 'approved'])
            ->first();
            
        if ($activeResignation) {
            return redirect()->route('employee.resignations.index')
                ->with('error', 'You already have an active resignation request.');
        }
        
        return view('employee.resignations.create');
    }

    /**
     * Store a newly created resignation request in storage.
     */
    public function store(Request $request)
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        
        // Check if employee already has an active resignation
        $activeResignation = EmployeeResignation::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'hr_approved', 'manager_approved', 'approved'])
            ->first();
            
        if ($activeResignation) {
            return redirect()->back()
                ->with('error', 'You already have an active resignation request.')
                ->withInput();
        }
        
        $validated = $request->validate([
            'resignation_type' => 'required|in:voluntary,retirement,contract_end',
            'reason' => 'required|string|max:1000',
            'resignation_date' => 'required|date|after_or_equal:today',
            'last_working_date' => 'required|date|after:resignation_date',
            'notice_period_days' => 'required|integer|min:0|max:365',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'employee_remarks' => 'nullable|string|max:500'
        ]);

        // Validate that last working date is reasonable based on notice period
        $resignationDate = Carbon::parse($validated['resignation_date']);
        $lastWorkingDate = Carbon::parse($validated['last_working_date']);
        $actualNoticeDays = $resignationDate->diffInDays($lastWorkingDate);
        
        if ($actualNoticeDays != $validated['notice_period_days']) {
            return redirect()->back()
                ->with('error', 'The notice period days must match the difference between resignation date and last working date.')
                ->withInput();
        }

        // Handle attachment upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = 'resignation_' . $employee->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('resignation-attachments', $fileName, 'public');
        }

        $resignation = EmployeeResignation::create([
            'employee_id' => $employee->id,
            'company_id' => $employee->company_id,
            'resignation_type' => $validated['resignation_type'],
            'reason' => $validated['reason'],
            'resignation_date' => $validated['resignation_date'],
            'last_working_date' => $validated['last_working_date'],
            'notice_period_days' => $validated['notice_period_days'],
            'attachment_path' => $attachmentPath,
            'employee_remarks' => $validated['employee_remarks'],
            'reporting_manager_id' => $employee->reporting_manager_id,
            'status' => 'pending'
        ]);

        // TODO: Send notification to HR and reporting manager

        return redirect()->route('employee.resignations.index')
            ->with('success', 'Resignation request submitted successfully. You will be notified once it is reviewed.');
    }

    /**
     * Display the specified resignation request.
     */
    public function show(EmployeeResignation $resignation)
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        
        // Check if the resignation belongs to the authenticated employee
        if ($resignation->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $resignation->load(['reportingManager', 'hrAdmin', 'approver', 'employee.department', 'employee.designation']);
        
        return view('employee.resignations.show', compact('resignation'));
    }

    /**
     * Withdraw the resignation request.
     */
    public function withdraw(EmployeeResignation $resignation)
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        
        // Check if the resignation belongs to the authenticated employee
        if ($resignation->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if resignation can be withdrawn
        if (!$resignation->canBeWithdrawn()) {
            return redirect()->back()
                ->with('error', 'Resignation cannot be withdrawn at this stage. Please contact HR if you need assistance.');
        }
        
        $resignation->update([
            'status' => 'withdrawn',
            'admin_remarks' => 'Withdrawn by employee on ' . now()->format('Y-m-d H:i:s')
        ]);
        
        // TODO: Send notification to HR and reporting manager about withdrawal
        
        return redirect()->route('employee.resignations.index')
            ->with('success', 'Resignation request withdrawn successfully.');
    }

    /**
     * Show the form for editing the specified resignation request.
     */
    public function edit(EmployeeResignation $resignation)
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        
        // Check if the resignation belongs to the authenticated employee
        if ($resignation->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if resignation can be edited (only pending status)
        if ($resignation->status !== 'pending') {
            return redirect()->route('employee.resignations.index')
                ->with('error', 'Only pending resignation requests can be edited.');
        }
        
        return view('employee.resignations.edit', compact('resignation'));
    }

    /**
     * Update the specified resignation request in storage.
     */
    public function update(Request $request, EmployeeResignation $resignation)
    {
        $employee = Employee::where('user_id', Auth::id())->firstOrFail();
        
        // Check if the resignation belongs to the authenticated employee
        if ($resignation->employee_id !== $employee->id) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if resignation can be updated (only pending status)
        if ($resignation->status !== 'pending') {
            return redirect()->route('employee.resignations.index')
                ->with('error', 'Only pending resignation requests can be updated.');
        }
        
        $validated = $request->validate([
            'resignation_type' => 'required|in:voluntary,retirement,contract_end',
            'reason' => 'required|string|max:1000',
            'resignation_date' => 'required|date|after_or_equal:today',
            'last_working_date' => 'required|date|after:resignation_date',
            'notice_period_days' => 'required|integer|min:0|max:365',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'employee_remarks' => 'nullable|string|max:500'
        ]);

        // Validate that last working date is reasonable based on notice period
        $resignationDate = Carbon::parse($validated['resignation_date']);
        $lastWorkingDate = Carbon::parse($validated['last_working_date']);
        $actualNoticeDays = $resignationDate->diffInDays($lastWorkingDate);
        
        if ($actualNoticeDays != $validated['notice_period_days']) {
            return redirect()->back()
                ->with('error', 'The notice period days must match the difference between resignation date and last working date.')
                ->withInput();
        }

        // Handle attachment upload
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($resignation->attachment_path) {
                Storage::disk('public')->delete($resignation->attachment_path);
            }
            
            $file = $request->file('attachment');
            $fileName = 'resignation_' . $employee->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $validated['attachment_path'] = $file->storeAs('resignation-attachments', $fileName, 'public');
        }

        $resignation->update($validated);

        return redirect()->route('employee.resignations.index')
            ->with('success', 'Resignation request updated successfully.');
    }
}