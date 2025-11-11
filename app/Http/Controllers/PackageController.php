<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageModule;
use App\Models\PackagePricingTier;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Services\AuditLogService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    protected PricingService $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->middleware('auth');
        $this->middleware('superadmin');
        $this->pricingService = $pricingService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Package::class);

        $query = Package::with(['packageModules', 'pricingTiers']);

        // Filtering
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('pricing_type') && !empty($request->pricing_type)) {
            $query->where('pricing_type', $request->pricing_type);
        }

        $perPage = $request->get('per_page', 15);
        $packages = $query->paginate($perPage);

        // Handle AJAX requests for real-time filtering
        if ($request->ajax()) {
            return response()->json([
                'html' => view('superadmin.packages.partials.table-body', compact('packages'))->render(),
                'pagination' => $packages->appends($request->query())->links()->toHtml(),
                'total' => $packages->total()
            ]);
        }

        return view('superadmin.packages.index', compact('packages'));
    }

    public function create()
    {
        $this->authorize('create', Package::class);

        // Get all slugs for module selection
        $slugs = \App\Models\Slug::with('children')->root()->orderBy('sort_order')->get();

        return view('superadmin.packages.create', compact('slugs'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Package::class);

        $request->validate([
            'name' => 'required|string|max:255|unique:packages',
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:one_time,subscription',
            'base_price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'nullable|required_if:pricing_type,subscription|in:monthly,yearly,quarterly',
            'modules' => 'nullable|array',
            'modules.*' => 'boolean',
            'pricing_tiers' => 'nullable|array',
            'pricing_tiers.*.name' => 'required_with:pricing_tiers|string|max:255',
            'pricing_tiers.*.min_users' => 'required_with:pricing_tiers|integer|min:1',
            'pricing_tiers.*.price' => 'required_with:pricing_tiers|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Convert checkbox values (1/0) to boolean (true/false)
            $modules = $request->modules ?? [];
            $convertedModules = [];
            foreach ($modules as $moduleSlug => $value) {
                $convertedModules[$moduleSlug] = $value == '1' ? true : false;
            }

            $package = Package::create([
                'name' => $request->name,
                'description' => $request->description,
                'pricing_type' => $request->pricing_type,
                'base_price' => $request->base_price,
                'currency' => $request->currency,
                'billing_cycle' => $request->pricing_type === 'one_time' ? null : $request->billing_cycle,
                'modules' => $convertedModules
            ]);

            // Handle pricing tiers
            if ($request->has('pricing_tiers') && is_array($request->pricing_tiers)) {
                foreach ($request->pricing_tiers as $tierData) {
                    PackagePricingTier::create([
                        'package_id' => $package->id,
                        'tier_name' => $tierData['name'],
                        'min_users' => $tierData['min_users'],
                        'max_users' => $tierData['max_users'] ?? null,
                        'price' => $tierData['price'],
                        'currency' => $request->currency,
                    ]);
                }
            }

            // Log audit
            AuditLogService::logCreated($package, 'Package created successfully');

            DB::commit();
            return redirect()->route('superadmin.packages.index')
                ->with('success', 'Package created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create package', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->withInput()->with('error', 'Failed to create package');
        }
    }

    public function show($id)
    {
        $package = Package::with(['packageModules', 'pricingTiers', 'companyPackages.company'])
            ->findOrFail($id);

        $this->authorize('view', $package);

        return view('superadmin.packages.show', compact('package'));
    }

    public function edit($id)
    {
        $package = Package::findOrFail($id);
        $this->authorize('update', $package);

        // Get all slugs for module selection
        $slugs = \App\Models\Slug::with('children')->root()->orderBy('sort_order')->get();

        return view('superadmin.packages.edit', compact('package', 'slugs'));
    }

    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);
        $this->authorize('update', $package);

        $request->validate([
            'name' => 'required|string|max:255|unique:packages,name,' . $id,
            'description' => 'nullable|string',
            'pricing_type' => 'required|in:one_time,subscription',
            'base_price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_cycle' => 'nullable|required_if:pricing_type,subscription|in:monthly,yearly,quarterly',
            'modules' => 'nullable|array',
            'modules.*' => 'boolean',
            'pricing_tiers' => 'nullable|array',
            'pricing_tiers.*.name' => 'required_with:pricing_tiers|string|max:255',
            'pricing_tiers.*.min_users' => 'required_with:pricing_tiers|integer|min:1',
            'pricing_tiers.*.price' => 'required_with:pricing_tiers|numeric|min:0',
        ]);

        $oldValues = $package->toArray();

        DB::beginTransaction();
        try {
            // Convert checkbox values (1/0) to boolean (true/false)
            $modules = $request->modules ?? [];
            $convertedModules = [];
            foreach ($modules as $moduleSlug => $value) {
                $convertedModules[$moduleSlug] = $value == '1' ? true : false;
            }

            $package->update([
                'name' => $request->name,
                'description' => $request->description,
                'pricing_type' => $request->pricing_type,
                'base_price' => $request->base_price,
                'currency' => $request->currency,
                'billing_cycle' => $request->pricing_type === 'one_time' ? null : $request->billing_cycle,
                'modules' => $convertedModules
            ]);

            // Handle pricing tiers - remove existing and add new ones
            $package->pricingTiers()->delete();
            if ($request->has('pricing_tiers') && is_array($request->pricing_tiers)) {
                foreach ($request->pricing_tiers as $tierData) {
                    PackagePricingTier::create([
                        'package_id' => $package->id,
                        'tier_name' => $tierData['name'],
                        'min_users' => $tierData['min_users'],
                        'max_users' => $tierData['max_users'] ?? null,
                        'price' => $tierData['price'],
                        'currency' => $request->currency,
                    ]);
                }
            }

            // Log audit
            AuditLogService::logUpdated($package, $oldValues, $package->toArray(), 'Package updated successfully');

            DB::commit();
            return redirect()->route('superadmin.packages.index')
                ->with('success', 'Package updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update package', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->withInput()->with('error', 'Failed to update package');
        }
    }

    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        $this->authorize('delete', $package);

        // Check if package is assigned to any companies
        if ($package->companyPackages()->exists()) {
            return back()->with('error', 'Cannot delete package that is assigned to companies');
        }

        DB::beginTransaction();
        try {
            $package->delete();

            // Log audit
            AuditLogService::logDeleted($package, 'Package deleted successfully');

            DB::commit();
            return redirect()->route('superadmin.packages.index')
                ->with('success', 'Package deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete package', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to delete package');
        }
    }

    public function toggleActive($id)
    {
        try {
            $package = Package::findOrFail($id);
            $this->authorize('toggleActive', $package);

            // Check if package is assigned to any companies (prevent deactivation of assigned packages)
            if ($package->is_active && $package->companyPackages()->exists()) {
                return response()->json([
                    'success' => false,
                    'is_active' => $package->is_active,
                    'message' => 'Cannot deactivate package that is assigned to companies'
                ]);
            }

            $oldValues = ['is_active' => $package->is_active];
            $newStatus = !$package->is_active;
            $statusText = $newStatus ? 'activated' : 'deactivated';

            DB::beginTransaction();
            try {
                $package->update(['is_active' => $newStatus]);

                // Log audit
                AuditLogService::logUpdated($package, $oldValues, ['is_active' => $newStatus], "Package {$statusText} successfully");

                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'is_active' => $newStatus,
                    'message' => "Package has been {$statusText} successfully"
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Failed to toggle package status', [
                    'error' => $e->getMessage(),
                    'package_id' => $id,
                    'user_id' => auth()->id()
                ]);
                return response()->json([
                    'success' => false,
                    'is_active' => $package->is_active,
                    'message' => 'Failed to update package status: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Package toggle error', [
                'error' => $e->getMessage(),
                'package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'is_active' => false,
                'message' => 'Package not found or access denied'
            ], 404);
        }
    }

    public function getModules()
    {
        try {
            // Get all available modules from the system
            // This could be from a config file or database table
            $modules = [
                ['name' => 'attendance', 'display_name' => 'Attendance Management'],
                ['name' => 'leave', 'display_name' => 'Leave Management'],
                ['name' => 'payroll', 'display_name' => 'Payroll Management'],
                ['name' => 'reimbursement', 'display_name' => 'Reimbursement'],
                ['name' => 'employee', 'display_name' => 'Employee Management'],
                ['name' => 'department', 'display_name' => 'Department Management'],
                ['name' => 'designation', 'display_name' => 'Designation Management'],
                ['name' => 'announcement', 'display_name' => 'Announcements'],
                ['name' => 'handbook', 'display_name' => 'Employee Handbook'],
                ['name' => 'asset', 'display_name' => 'Asset Management'],
                ['name' => 'field_visit', 'display_name' => 'Field Visit'],
                ['name' => 'lead', 'display_name' => 'Lead Management'],
            ];

            return response()->json([
                'success' => true,
                'modules' => $modules
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get modules', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load modules'
            ], 500);
        }
    }

    public function calculatePrice(Request $request, $id)
    {
        try {
            $package = Package::findOrFail($id);
            $this->authorize('view', $package);

            $request->validate([
                'user_count' => 'required|integer|min:1',
                'discount_code' => 'nullable|string',
            ]);

            $pricing = $this->pricingService->calculatePackagePrice(
                $package,
                $request->user_count,
                $request->discount_code
            );

            return response()->json([
                'success' => true,
                'pricing' => $pricing
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to calculate package price', [
                'error' => $e->getMessage(),
                'package_id' => $id,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate package price'
            ], 500);
        }
    }
}