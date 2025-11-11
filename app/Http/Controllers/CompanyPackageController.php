<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyPackage;
use App\Models\Package;
use App\Http\Requests\AssignPackageRequest;
use App\Http\Requests\BulkAssignPackageRequest;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('superadmin');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', CompanyPackage::class);

        $query = CompanyPackage::with(['company', 'package', 'assignedBy']);

        // Filtering
        if ($request->has('company_id') && !empty($request->company_id)) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('package_id') && !empty($request->package_id)) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $companyPackages = $query->paginate(15);
        $companies = Company::all();
        $packages = Package::active()->get();

        return view('superadmin.company-packages.index', compact('companyPackages', 'companies', 'packages'));
    }

    public function create()
    {
        $this->authorize('assign', CompanyPackage::class);

        $companies = Company::all();
        $packages = Package::active()->get();

        return view('superadmin.company-packages.assign', compact('companies', 'packages'));
    }

    public function edit($id)
    {
        try {
            $companyPackage = CompanyPackage::with(['company', 'package', 'assignedBy', 'invoices'])
                ->findOrFail($id);

            $this->authorize('update', $companyPackage);

            $packages = Package::active()->get();

            return view('superadmin.company-packages.edit', compact('companyPackage', 'packages'));
        } catch (\Exception $e) {
            Log::error('Failed to edit company package', [
                'error' => $e->getMessage(),
                'company_package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return redirect()->route('superadmin.company-packages.index')
                ->with('error', 'Company package not found');
        }
    }

    public function update(Request $request, $id)
    {
        $companyPackage = CompanyPackage::findOrFail($id);
        $this->authorize('update', $companyPackage);

        $request->validate([
            'assigned_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:assigned_at',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $companyPackage->toArray();

            $updateData = [
                'assigned_at' => $request->assigned_at ? new \DateTime($request->assigned_at) : $companyPackage->assigned_at,
                'expires_at' => $request->expires_at ? new \DateTime($request->expires_at) : null,
                'notes' => $request->notes,
            ];

            // Handle status change
            if ($request->has('is_active') && $request->is_active !== $companyPackage->is_active) {
                $updateData['is_active'] = $request->boolean('is_active');
                if ($updateData['is_active']) {
                    $updateData['activated_at'] = now();
                    $updateData['deactivated_at'] = null;
                } else {
                    $updateData['deactivated_at'] = now();
                }
            }

            $companyPackage->update($updateData);

            // Handle status change and update role permissions accordingly
            if (isset($updateData['is_active']) && $updateData['is_active'] !== $oldValues['is_active']) {
                if ($updateData['is_active']) {
                    // Package is being activated, update permissions with current package
                    $this->updateCompanyRolePermissions($companyPackage->company_id, $companyPackage->package_id);
                } else {
                    // Package is being deactivated, clear permissions
                    $this->clearCompanyRolePermissions($companyPackage->company_id);
                }
            }

            // Log audit
            AuditLogService::log('updated', $companyPackage, $oldValues, $companyPackage->fresh()->toArray(), 'Company package updated successfully');

            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Company package updated successfully',
                    'company_package' => $companyPackage->fresh()->load(['company', 'package', 'assignedBy'])
                ]);
            }

            return redirect()->route('superadmin.company-packages.index')
                ->with('success', 'Company package updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update company package', [
                'error' => $e->getMessage(),
                'company_package_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update company package: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('superadmin.company-packages.edit', $id)
                ->with('error', 'Failed to update company package: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $companyPackage = CompanyPackage::with(['company', 'package', 'assignedBy', 'invoices'])
                ->findOrFail($id);

            $this->authorize('view', $companyPackage);

            return view('superadmin.company-packages.show', compact('companyPackage'));
        } catch (\Exception $e) {
            Log::error('Failed to show company package', [
                'error' => $e->getMessage(),
                'company_package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return redirect()->route('superadmin.company-packages.index')
                ->with('error', 'Company package not found');
        }
    }

    public function destroy($id)
    {
        return $this->unassign($id);
    }

    public function assign(Request $request)
    {
        $this->authorize('assign', CompanyPackage::class);

        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'package_id' => 'required|exists:packages,id',
            'assigned_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:assigned_at',
            'send_notification' => 'boolean',
            'generate_invoice' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Check if company already has an active package
            $existingActive = CompanyPackage::where('company_id', $request->company_id)
                ->active()
                ->first();

            if ($existingActive) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company already has an active package. Please reassign or deactivate the current package first.'
                ], 422);
            }

            $companyPackage = CompanyPackage::create([
                'company_id' => $request->company_id,
                'package_id' => $request->package_id,
                'assigned_by' => auth()->id(),
                'assigned_at' => $request->assigned_at ?? now(),
                'expires_at' => $request->expires_at,
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // Update role permissions based on package modules
            $this->updateCompanyRolePermissions($request->company_id, $request->package_id);

            // Generate invoice if requested
            if ($request->boolean('generate_invoice')) {
                $billingService = app(\App\Services\BillingService::class);
                $billingService->generateInvoice($companyPackage, now(), now()->addMonth());
            }

            // Log audit
            AuditLogService::logAssigned($companyPackage, 'Package assigned to company successfully');

            DB::commit();
            return redirect()->route('superadmin.company-packages.index')->with('success', 'Package assigned to company successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign package', [
                'error' => $e->getMessage(),
                'company_id' => $request->company_id,
                'package_id' => $request->package_id,
                'user_id' => auth()->id()
            ]);
            return redirect()->route('superadmin.company-packages.index')->with('error', 'Failed to assign package: ' . $e->getMessage());
        }
    }

    /**
     * Update company role permissions based on package modules
     */
    private function updateCompanyRolePermissions($companyId, $packageId)
    {
        try {
            // Get the package with its modules
            $package = Package::findOrFail($packageId);
            
            // Update all roles for this company
            $roles = \App\Models\Role::where('company_id', $companyId)->get();
            
            foreach ($roles as $role) {
                $role->update([
                    'permissions' => $package->modules
                ]);
            }
            
            Log::info('Updated role permissions for company', [
                'company_id' => $companyId,
                'package_id' => $packageId,
                'roles_updated' => $roles->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update role permissions', [
                'error' => $e->getMessage(),
                'company_id' => $companyId,
                'package_id' => $packageId
            ]);
            throw $e; // Re-throw to trigger rollback
        }
    }

    /**
     * Clear company role permissions
     */
    private function clearCompanyRolePermissions($companyId)
    {
        try {
            // Update all roles for this company to clear permissions
            $roles = \App\Models\Role::where('company_id', $companyId)->get();
            
            foreach ($roles as $role) {
                $role->update([
                    'permissions' => null
                ]);
            }
            
            Log::info('Cleared role permissions for company', [
                'company_id' => $companyId,
                'roles_updated' => $roles->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to clear role permissions', [
                'error' => $e->getMessage(),
                'company_id' => $companyId
            ]);
            throw $e; // Re-throw to trigger rollback
        }
    }

    public function reassign(Request $request, $id)
    {
        $companyPackage = CompanyPackage::findOrFail($id);
        $this->authorize('reassign', $companyPackage);

        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        // Check if new package is active
        $newPackage = Package::find($request->package_id);
        if (!$newPackage->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot assign to an inactive package'
            ], 422);
        }

        // If current assignment is active, validate that a different package is selected
        if ($companyPackage->is_active && $request->package_id == $companyPackage->package_id) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a different package for reassignment'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $oldValues = $companyPackage->toArray();

            // Deactivate current package
            $companyPackage->update([
                'is_active' => false,
                'deactivated_at' => now()
            ]);

            // Create new assignment
            $newAssignment = CompanyPackage::create([
                'company_id' => $companyPackage->company_id,
                'package_id' => $request->package_id,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // Update role permissions based on new package modules
            $this->updateCompanyRolePermissions($companyPackage->company_id, $request->package_id);

            // Log audit
            AuditLogService::log('reassigned', $companyPackage, $oldValues, $newAssignment->toArray(), 'Package reassigned for company successfully');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Package reassigned successfully',
                'company_package' => $newAssignment->load(['company', 'package'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reassign package', [
                'error' => $e->getMessage(),
                'company_package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to reassign package: ' . $e->getMessage()
            ], 500);
        }
    }

    public function unassign($id)
    {
        $companyPackage = CompanyPackage::findOrFail($id);
        $this->authorize('delete', $companyPackage);

        DB::beginTransaction();
        try {
            $companyPackage->deactivate();

            // Clear role permissions when package is unassigned
            $this->clearCompanyRolePermissions($companyPackage->company_id);

            // Log audit
            AuditLogService::logDeleted($companyPackage, 'Package unassigned from company successfully');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Package unassigned successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to unassign package', [
                'error' => $e->getMessage(),
                'company_package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to unassign package'
            ], 500);
        }
    }

    public function getCompanyPackages($companyId)
    {
        try {
            $companyPackages = CompanyPackage::with(['package', 'assignedBy', 'invoices'])
                ->where('company_id', $companyId)
                ->orderBy('assigned_at', 'desc')
                ->get();

            // Add billing information to each package
            $companyPackages->transform(function ($companyPackage) {
                $companyPackage->billing_info = [
                    'current_invoice' => $companyPackage->getCurrentInvoice(),
                    'next_billing_date' => $companyPackage->calculateNextBillingDate(),
                    'is_overdue' => $companyPackage->isOverdue(),
                    'invoice_history_count' => $companyPackage->invoices()->count(),
                ];
                return $companyPackage;
            });

            return response()->json([
                'success' => true,
                'company_packages' => $companyPackages
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get company packages', [
                'error' => $e->getMessage(),
                'company_id' => $companyId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load company packages'
            ], 500);
        }
    }

    public function getBillingInfo($id)
    {
        try {
            $companyPackage = CompanyPackage::with(['package', 'invoices', 'company'])
                ->findOrFail($id);

            $this->authorize('view', $companyPackage);

            $billingInfo = [
                'company_package' => $companyPackage,
                'current_invoice' => $companyPackage->getCurrentInvoice(),
                'invoice_history' => $companyPackage->getInvoiceHistory(),
                'next_billing_date' => $companyPackage->calculateNextBillingDate(),
                'is_overdue' => $companyPackage->isOverdue(),
                'total_invoiced' => $companyPackage->invoices()->sum('total_amount'),
                'total_paid' => $companyPackage->invoices()->where('status', 'paid')->sum('total_amount'),
            ];

            return response()->json([
                'success' => true,
                'billing_info' => $billingInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get billing info', [
                'error' => $e->getMessage(),
                'company_package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load billing information'
            ], 500);
        }
    }

    public function bulkAssign(Request $request)
    {
        $this->authorize('assign', CompanyPackage::class);

        $request->validate([
            'company_ids' => 'required|array|min:1',
            'company_ids.*' => 'exists:companies,id',
            'package_id' => 'required|exists:packages,id',
        ]);

        DB::beginTransaction();
        try {
            $assigned = [];
            $errors = [];

            foreach ($request->company_ids as $companyId) {
                try {
                    // Check if company already has an active package
                    $existingActive = CompanyPackage::where('company_id', $companyId)
                        ->active()
                        ->first();

                    if ($existingActive) {
                        $errors[] = "Company ID {$companyId} already has an active package";
                        continue;
                    }

                    $companyPackage = CompanyPackage::create([
                        'company_id' => $companyId,
                        'package_id' => $request->package_id,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => now(),
                        'is_active' => true,
                        'activated_at' => now(),
                    ]);

                    $assigned[] = $companyPackage;
                    
                    // Update role permissions for each assigned company
                    $this->updateCompanyRolePermissions($companyId, $request->package_id);
                } catch (\Exception $e) {
                    $errors[] = "Failed to assign package to company ID {$companyId}: " . $e->getMessage();
                }
            }

            // Log audit for bulk assignment
            AuditLogService::log('bulk_assigned', new CompanyPackage(), [], ['package_id' => $request->package_id, 'assigned_count' => count($assigned)], 'Bulk package assignment completed successfully');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Bulk assignment completed. Assigned to " . count($assigned) . " companies.",
                'assigned' => $assigned,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed bulk package assignment', [
                'error' => $e->getMessage(),
                'package_id' => $request->package_id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete bulk assignment'
            ], 500);
        }
    }

    public function toggleActive($id)
    {
        try {
            $companyPackage = CompanyPackage::findOrFail($id);
            $this->authorize('update', $companyPackage);

            $oldValues = ['is_active' => $companyPackage->is_active];
            $newStatus = !$companyPackage->is_active;
            $statusText = $newStatus ? 'activated' : 'deactivated';

            DB::beginTransaction();
            try {
                $companyPackage->update([
                    'is_active' => $newStatus,
                    'deactivated_at' => $newStatus ? null : now(),
                    'activated_at' => $newStatus ? now() : null
                ]);

                // Update role permissions based on package status
                if ($newStatus) {
                    // Package is being activated, update permissions with current package
                    $this->updateCompanyRolePermissions($companyPackage->company_id, $companyPackage->package_id);
                } else {
                    // Package is being deactivated, clear permissions
                    $this->clearCompanyRolePermissions($companyPackage->company_id);
                }

                // Log audit
                AuditLogService::log('status_toggled', $companyPackage, $oldValues, ['is_active' => $newStatus], "Assignment {$statusText} successfully");

                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'is_active' => $newStatus,
                    'message' => "Assignment has been {$statusText} successfully"
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to toggle assignment status', [
                    'error' => $e->getMessage(),
                    'company_package_id' => $id,
                    'user_id' => auth()->id()
                ]);
                return response()->json([
                    'success' => false,
                    'is_active' => $companyPackage->is_active,
                    'message' => 'Failed to update assignment status: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Assignment toggle error', [
                'error' => $e->getMessage(),
                'company_package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'is_active' => false,
                'message' => 'Assignment not found or access denied'
            ], 404);
        }
    }

    
}