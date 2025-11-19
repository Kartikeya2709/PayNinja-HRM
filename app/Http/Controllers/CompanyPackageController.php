<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyPackage;
use App\Models\Package;
use App\Http\Requests\AssignPackageRequest;
use App\Http\Requests\BulkAssignPackageRequest;
use App\Services\AuditLogService;
use App\Services\PermissionTransactionService;
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
                $permissionService = app(PermissionTransactionService::class);

                try {
                    $permissionService->executeWithPermissionSync(
                        function () {
                            // Status change already handled above, just sync permissions
                            return true;
                        },
                        $companyPackage->company_id,
                        $updateData['is_active'] ? $companyPackage->package_id : null,
                        [
                            'action' => 'status_change',
                            'old_status' => $oldValues['is_active'],
                            'new_status' => $updateData['is_active'],
                            'package_name' => $companyPackage->package->name ?? 'Unknown'
                        ]
                    );
                } catch (\Exception $e) {
                    // Log permission sync failure but don't fail the entire update
                    Log::error('Permission sync failed during status change', [
                        'company_package_id' => $companyPackage->id,
                        'error' => $e->getMessage()
                    ]);
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

        $permissionService = app(PermissionTransactionService::class);

        try {
            $companyPackage = $permissionService->executeWithPermissionSync(
                function () use ($request) {
                    // Check if company already has an active package
                    $existingActive = CompanyPackage::where('company_id', $request->company_id)
                        ->active()
                        ->first();

                    if ($existingActive) {
                        throw new \Exception('Company already has an active package. Please reassign or deactivate the current package first.');
                    }

                    // Create the package assignment
                    $companyPackage = CompanyPackage::create([
                        'company_id' => $request->company_id,
                        'package_id' => $request->package_id,
                        'assigned_by' => auth()->id(),
                        'assigned_at' => $request->assigned_at ?? now(),
                        'expires_at' => $request->expires_at,
                        'is_active' => true,
                        'activated_at' => now(),
                    ]);

                    // Generate invoice if requested
                    if ($request->boolean('generate_invoice')) {
                        $billingService = app(\App\Services\BillingService::class);
                        $billingService->generateInvoice($companyPackage, now(), now()->addMonth());
                    }

                    return $companyPackage;
                },
                $request->company_id,
                $request->package_id,
                [
                    'action' => 'assign',
                    'package_name' => Package::find($request->package_id)->name ?? 'Unknown',
                    'generate_invoice' => $request->boolean('generate_invoice')
                ]
            );

            // Log successful assignment
            AuditLogService::logAssigned($companyPackage, 'Package assigned to company successfully with permissions synchronized');

            return redirect()->route('superadmin.company-packages.index')
                ->with('success', 'Package assigned to company successfully with permissions updated');

        } catch (\Exception $e) {
            Log::error('Failed to assign package', [
                'error' => $e->getMessage(),
                'company_id' => $request->company_id,
                'package_id' => $request->package_id,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('superadmin.company-packages.index')
                ->with('error', 'Failed to assign package: ' . $e->getMessage());
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

        $permissionService = app(PermissionTransactionService::class);

        try {
            $result = $permissionService->executeWithPermissionSync(
                function () use ($companyPackage, $request) {
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

                    return [
                        'old_assignment' => $companyPackage,
                        'new_assignment' => $newAssignment,
                        'old_values' => $oldValues
                    ];
                },
                $companyPackage->company_id,
                $request->package_id,
                [
                    'action' => 'reassign',
                    'old_package_id' => $companyPackage->package_id,
                    'old_package_name' => $companyPackage->package->name ?? 'Unknown',
                    'new_package_name' => $newPackage->name ?? 'Unknown'
                ]
            );

            // Log successful reassignment
            AuditLogService::log('reassigned', $result['old_assignment'], $result['old_values'], $result['new_assignment']->toArray(), 'Package reassigned for company successfully with permissions synchronized');

            return response()->json([
                'success' => true,
                'message' => 'Package reassigned successfully',
                'company_package' => $result['new_assignment']->load(['company', 'package'])
            ]);
        } catch (\Exception $e) {
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

        $permissionService = app(PermissionTransactionService::class);

        try {
            $permissionService->executeWithPermissionSync(
                function () use ($companyPackage) {
                    $companyPackage->deactivate();
                    return $companyPackage;
                },
                $companyPackage->company_id,
                null, // Clear permissions
                [
                    'action' => 'unassign',
                    'package_name' => $companyPackage->package->name ?? 'Unknown'
                ]
            );

            // Log successful unassignment
            AuditLogService::logDeleted($companyPackage, 'Package unassigned from company successfully with permissions cleared');

            return response()->json([
                'success' => true,
                'message' => 'Package unassigned successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to unassign package', [
                'error' => $e->getMessage(),
                'company_package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to unassign package: ' . $e->getMessage()
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

        $permissionService = app(PermissionTransactionService::class);
        $assigned = [];
        $errors = [];

        foreach ($request->company_ids as $companyId) {
            try {
                $companyPackage = $permissionService->executeWithPermissionSync(
                    function () use ($companyId, $request) {
                        // Check if company already has an active package
                        $existingActive = CompanyPackage::where('company_id', $companyId)
                            ->active()
                            ->first();

                        if ($existingActive) {
                            throw new \Exception('Company already has an active package');
                        }

                        // Create the package assignment
                        return CompanyPackage::create([
                            'company_id' => $companyId,
                            'package_id' => $request->package_id,
                            'assigned_by' => auth()->id(),
                            'assigned_at' => now(),
                            'is_active' => true,
                            'activated_at' => now(),
                        ]);
                    },
                    $companyId,
                    $request->package_id,
                    [
                        'action' => 'bulk_assign',
                        'batch_id' => uniqid('bulk_'),
                        'package_name' => Package::find($request->package_id)->name ?? 'Unknown'
                    ]
                );

                $assigned[] = $companyPackage;

            } catch (\Exception $e) {
                $errors[] = "Failed to assign package to company ID {$companyId}: " . $e->getMessage();
                Log::error('Bulk assignment failed for company', [
                    'company_id' => $companyId,
                    'package_id' => $request->package_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Log bulk assignment summary
        AuditLogService::log('bulk_assigned', new CompanyPackage(), [], [
            'package_id' => $request->package_id,
            'assigned_count' => count($assigned),
            'error_count' => count($errors),
            'company_ids' => $request->company_ids
        ], 'Bulk package assignment completed with permissions synchronized');

        return response()->json([
            'success' => true,
            'message' => "Bulk assignment completed. Assigned to " . count($assigned) . " companies.",
            'assigned' => $assigned,
            'errors' => $errors
        ]);
    }

    public function toggleActive($id)
    {
        try {
            $companyPackage = CompanyPackage::findOrFail($id);
            $this->authorize('update', $companyPackage);

            $oldValues = ['is_active' => $companyPackage->is_active];
            $newStatus = !$companyPackage->is_active;
            $statusText = $newStatus ? 'activated' : 'deactivated';

            $permissionService = app(PermissionTransactionService::class);

            $permissionService->executeWithPermissionSync(
                function () use ($companyPackage, $newStatus) {
                    $companyPackage->update([
                        'is_active' => $newStatus,
                        'deactivated_at' => $newStatus ? null : now(),
                        'activated_at' => $newStatus ? now() : null
                    ]);
                    return $companyPackage;
                },
                $companyPackage->company_id,
                $newStatus ? $companyPackage->package_id : null, // Set or clear permissions
                [
                    'action' => 'toggle_status',
                    'old_status' => $oldValues['is_active'],
                    'new_status' => $newStatus,
                    'package_name' => $companyPackage->package->name ?? 'Unknown'
                ]
            );

            // Log successful status change
            AuditLogService::log('status_toggled', $companyPackage, $oldValues, ['is_active' => $newStatus], "Assignment {$statusText} successfully with permissions synchronized");

            return response()->json([
                'success' => true,
                'is_active' => $newStatus,
                'message' => "Assignment has been {$statusText} successfully"
            ]);
        } catch (\Exception $e) {
            Log::error('Assignment toggle error', [
                'error' => $e->getMessage(),
                'company_package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'is_active' => false,
                'message' => 'Failed to toggle assignment status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate company permission state (for debugging/admin purposes)
     */
    public function validatePermissionState($companyId)
    {
        try {
            $this->authorize('viewAny', CompanyPackage::class);

            $permissionService = app(PermissionTransactionService::class);
            $validationResult = $permissionService->validateCompanyPermissionState($companyId);

            return response()->json([
                'success' => true,
                'validation_result' => $validationResult
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to validate permission state', [
                'error' => $e->getMessage(),
                'company_id' => $companyId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate permission state: ' . $e->getMessage()
            ], 500);
        }
    }

    
}