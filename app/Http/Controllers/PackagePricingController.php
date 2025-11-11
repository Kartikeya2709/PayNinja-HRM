<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackagePricingTier;
use App\Http\Requests\StorePricingTierRequest;
use App\Http\Requests\UpdatePricingTierRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PackagePricingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:superadmin');
    }

    public function storeTier(StorePricingTierRequest $request, $packageId)
    {
        $package = Package::findOrFail($packageId);

        DB::beginTransaction();
        try {
            $tier = PackagePricingTier::create([
                'package_id' => $packageId,
                ...$request->validated()
            ]);

            // Log audit
            Log::info('Pricing tier added to package', [
                'package_id' => $packageId,
                'tier_id' => $tier->id,
                'tier_name' => $tier->tier_name,
                'user_id' => auth()->id(),
                'action' => 'add_tier'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pricing tier added successfully',
                'tier' => $tier
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add pricing tier', [
                'error' => $e->getMessage(),
                'package_id' => $packageId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to add pricing tier'
            ], 500);
        }
    }

    public function updateTier(UpdatePricingTierRequest $request, $tierId)
    {
        $tier = PackagePricingTier::findOrFail($tierId);

        DB::beginTransaction();
        try {
            $oldData = $tier->toArray();
            $tier->update($request->validated());

            // Log audit
            Log::info('Pricing tier updated', [
                'tier_id' => $tierId,
                'package_id' => $tier->package_id,
                'old_data' => $oldData,
                'new_data' => $tier->toArray(),
                'user_id' => auth()->id(),
                'action' => 'update_tier'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pricing tier updated successfully',
                'tier' => $tier
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update pricing tier', [
                'error' => $e->getMessage(),
                'tier_id' => $tierId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update pricing tier'
            ], 500);
        }
    }

    public function deleteTier($tierId)
    {
        $tier = PackagePricingTier::findOrFail($tierId);

        DB::beginTransaction();
        try {
            $tierData = $tier->toArray();
            $tier->delete();

            // Log audit
            Log::info('Pricing tier deleted', [
                'tier_id' => $tierId,
                'package_id' => $tier->package_id,
                'tier_data' => $tierData,
                'user_id' => auth()->id(),
                'action' => 'delete_tier'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pricing tier deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete pricing tier', [
                'error' => $e->getMessage(),
                'tier_id' => $tierId,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete pricing tier'
            ], 500);
        }
    }

    public function calculatePrice(Request $request, $packageId)
    {
        $request->validate([
            'user_count' => 'required|integer|min:1',
        ]);

        try {
            $package = Package::findOrFail($packageId);
            $price = $package->getPriceForUsers($request->user_count);

            return response()->json([
                'success' => true,
                'price' => $price,
                'currency' => $package->currency,
                'user_count' => $request->user_count
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to calculate price', [
                'error' => $e->getMessage(),
                'package_id' => $packageId,
                'user_count' => $request->user_count,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate price'
            ], 500);
        }
    }
}