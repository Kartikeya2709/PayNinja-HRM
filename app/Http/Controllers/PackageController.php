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

    public function store(StorePackageRequest $request)
    {
        $this->authorize('create', Package::class);

        DB::beginTransaction();
        try {
            $package = Package::create($request->validated());

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

    public function update(UpdatePackageRequest $request, $id)
    {
        $package = Package::findOrFail($id);
        $this->authorize('update', $package);

        $oldValues = $package->toArray();

        DB::beginTransaction();
        try {
            $package->update($request->validated());

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
            $package->update(['is_active' => false]);

            // Log audit
            AuditLogService::logDeleted($package, 'Package deactivated successfully');

            DB::commit();
            return redirect()->route('superadmin.packages.index')
                ->with('success', 'Package deactivated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to deactivate package', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to deactivate package');
        }
    }

    public function toggleActive($id)
    {
        $package = Package::findOrFail($id);
        $this->authorize('toggleActive', $package);

        $oldValues = ['is_active' => $package->is_active];

        DB::beginTransaction();
        try {
            $package->update(['is_active' => !$package->is_active]);

            // Log audit
            AuditLogService::logUpdated($package, $oldValues, ['is_active' => $package->is_active], 'Package status toggled successfully');

            DB::commit();
            return response()->json([
                'success' => true,
                'is_active' => $package->is_active,
                'message' => 'Package status updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to toggle package status', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update package status'
            ], 500);
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