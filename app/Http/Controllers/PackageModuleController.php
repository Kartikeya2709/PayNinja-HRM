<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageModule;
use App\Http\Requests\UpdatePackageModulesRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackageModuleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function updateModules(UpdatePackageModulesRequest $request, $packageId)
    {
        $package = Package::findOrFail($packageId);

        DB::beginTransaction();
        try {
            $modules = $request->modules ?? [];

            // Get current modules for audit logging
            $currentModules = $package->packageModules->pluck('module_name', 'id')->toArray();

            // Delete existing modules
            $package->packageModules()->delete();

            // Create new modules
            $newModules = [];
            foreach ($modules as $moduleData) {
                $newModules[] = PackageModule::create([
                    'package_id' => $packageId,
                    'module_name' => $moduleData['name'],
                    'has_access' => $moduleData['has_access'] ?? true,
                ]);
            }

            // Log audit
            Log::info('Package modules updated', [
                'package_id' => $packageId,
                'old_modules' => $currentModules,
                'new_modules' => collect($newModules)->pluck('module_name')->toArray(),
                'user_id' => auth()->id(),
                'action' => 'update_modules'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Package modules updated successfully',
                'modules' => $newModules
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update package modules', [
                'error' => $e->getMessage(),
                'package_id' => $packageId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update package modules'
            ], 500);
        }
    }

    public function getPackageModules($packageId)
    {
        try {
            $package = Package::findOrFail($packageId);
            $modules = $package->packageModules()->get();

            return response()->json([
                'success' => true,
                'modules' => $modules
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get package modules', [
                'error' => $e->getMessage(),
                'package_id' => $packageId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load package modules'
            ], 500);
        }
    }
}