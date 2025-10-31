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

    public function assign(AssignPackageRequest $request)
    {
        $this->authorize('assign', CompanyPackage::class);

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
                'assigned_at' => now(),
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // Log audit
            AuditLogService::logAssigned($companyPackage, 'Package assigned to company successfully');

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Package assigned successfully',
                'company_package' => $companyPackage->load(['company', 'package'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign package', [
                'error' => $e->getMessage(),
                'company_id' => $request->company_id,
                'package_id' => $request->package_id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign package'
            ], 500);
        }
    }

    public function reassign(Request $request, $id)
    {
        $companyPackage = CompanyPackage::findOrFail($id);
        $this->authorize('reassign', $companyPackage);

        $request->validate([
            'package_id' => 'required|exists:packages,id|different:package_id',
        ]);

        DB::beginTransaction();
        try {
            // Deactivate current package
            $companyPackage->deactivate();

            // Create new assignment
            $newAssignment = CompanyPackage::create([
                'company_id' => $companyPackage->company_id,
                'package_id' => $request->package_id,
                'assigned_by' => auth()->id(),
                'assigned_at' => now(),
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // Log audit
            AuditLogService::logReassigned($newAssignment, 'Package reassigned for company successfully');

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
                'message' => 'Failed to reassign package'
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

    public function bulkAssign(BulkAssignPackageRequest $request)
    {
        $this->authorize('assign', CompanyPackage::class);

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
}