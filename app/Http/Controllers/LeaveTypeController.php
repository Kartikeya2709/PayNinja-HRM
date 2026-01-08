<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class LeaveTypeController extends Controller
{
    /**
     * Get encrypted ID from model ID
     */
    private function getEncryptedId($id)
    {
        return Crypt::encrypt($id);
    }

    /**
     * Get decrypted ID from encrypted string
     */
    private function getDecryptedId($encryptedId)
    {
        try {
            return Crypt::decrypt($encryptedId);
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Get model from encrypted ID
     */
    private function getLeaveTypeFromEncryptedId(string $encryptedId): LeaveType
    {
        try {
            $id = Crypt::decrypt($encryptedId);
            return LeaveType::findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
    }
    /**
     * Display a listing of the leave types.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companyId = Auth::user()->company_id;
        $leaveTypes = LeaveType::where('company_id', $companyId)->get();

        return view('company.leave_types.index', compact('leaveTypes'));
    }

    /**
     * Show the form for creating a new leave type.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('company.leave_types.create');
    }

    /**
     * Store a newly created leave type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'description' => 'nullable|string',
            'default_days' => 'required|integer|min:0',
            'requires_attachment' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['company_id'] = $companyId;

        LeaveType::create($validated);

        return redirect()->route('leaves.leave-types.index')
            ->with('success', 'Leave type created successfully.');
    }

    /**
     * Show the form for editing the specified leave type.
     *
     * @param  \App\Models\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function edit($encryptedId)
    {
        $leaveType = $this->getLeaveTypeFromEncryptedId($encryptedId);
        // Check if leave type belongs to the company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        return view('company.leave_types.edit', compact('leaveType'));
    }

    /**
     * Update the specified leave type in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $encryptedId)
    {
        $leaveType = $this->getLeaveTypeFromEncryptedId($encryptedId);
        // Check if leave type belongs to the company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        $companyId = Auth::user()->company_id;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($leaveType->id),
            ],
            'description' => 'nullable|string',
            'default_days' => 'required|integer|min:0',
            'requires_attachment' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $leaveType->update($validated);

        return redirect()->route('leaves.leave-types.index')
            ->with('success', 'Leave type updated successfully.');
    }

    /**
     * Remove the specified leave type from storage.
     *
     * @param  \App\Models\LeaveType  $leaveType
     * @return \Illuminate\Http\Response
     */
    public function destroy($encryptedId)
    {
        $leaveType = $this->getLeaveTypeFromEncryptedId($encryptedId);
        // Check if leave type belongs to the company
        if ($leaveType->company_id !== Auth::user()->company_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if leave type is being used
        if ($leaveType->leaveBalances()->count() > 0 || $leaveType->leaveRequests()->count() > 0) {
            return redirect()->route('leaves.leave-types.index')
                ->with('error', 'Cannot delete leave type as it is being used.');
        }

        $leaveType->delete();

        return redirect()->route('leaves.leave-types.index')
            ->with('success', 'Leave type deleted successfully.');
    }
}
