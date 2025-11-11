<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use App\Http\Requests\StoreDiscountRequest;
use App\Http\Requests\UpdateDiscountRequest;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DiscountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('superadmin');
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Discount::class);

        $query = Discount::query();

        // Filtering
        if ($request->has('search') && !empty($request->search)) {
            $query->where('code', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('discount_type') && !empty($request->discount_type)) {
            $query->where('discount_type', $request->discount_type);
        }

        $discounts = $query->paginate(15);

        return view('superadmin.discounts.index', compact('discounts'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Discount::class);

        $request->validate([
            'code' => 'required|string|max:50|unique:discounts',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'applicable_packages' => 'nullable|array',
            'applicable_packages.*' => 'exists:packages,id',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $discount = Discount::create($request->only([
                'code', 'description', 'discount_type', 'discount_value',
                'max_discount_amount', 'valid_from', 'valid_until',
                'usage_limit', 'applicable_packages', 'is_active'
            ]));

            // Log audit
            AuditLogService::logCreated($discount, 'Discount created successfully');

            DB::commit();
            return redirect()->route('superadmin.discounts.index')
                ->with('success', 'Discount created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create discount', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return back()->withInput()->with('error', 'Failed to create discount');
        }
    }

    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);
        $this->authorize('update', $discount);

        $request->validate([
            'code' => 'required|string|max:50|unique:discounts,code,' . $id,
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed_amount',
            'discount_value' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'applicable_packages' => 'nullable|array',
            'applicable_packages.*' => 'exists:packages,id',
            'is_active' => 'boolean',
        ]);

        $oldValues = $discount->toArray();

        DB::beginTransaction();
        try {
            $discount->update($request->only([
                'code', 'description', 'discount_type', 'discount_value',
                'max_discount_amount', 'valid_from', 'valid_until',
                'usage_limit', 'applicable_packages', 'is_active'
            ]));

            // Log audit
            AuditLogService::logUpdated($discount, $oldValues, $discount->toArray(), 'Discount updated successfully');

            DB::commit();
            return redirect()->route('superadmin.discounts.index')
                ->with('success', 'Discount updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update discount', [
                'error' => $e->getMessage(),
                'discount_id' => $id,
                'user_id' => auth()->id()
            ]);
            return back()->withInput()->with('error', 'Failed to update discount');
        }
    }

    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $this->authorize('delete', $discount);

        // Check if discount is used in any invoices
        if ($discount->invoices()->exists()) {
            return back()->with('error', 'Cannot delete discount that is used in invoices');
        }

        DB::beginTransaction();
        try {
            $discount->delete();

            // Log audit
            AuditLogService::logDeleted($discount, 'Discount deleted successfully');

            DB::commit();
            return redirect()->route('superadmin.discounts.index')
                ->with('success', 'Discount deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete discount', [
                'error' => $e->getMessage(),
                'discount_id' => $id,
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Failed to delete discount');
        }
    }

    public function validateCode(Request $request)
    {
        $this->authorize('validate', Discount::class);

        $request->validate([
            'code' => 'required|string',
            'amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $discount = Discount::where('code', $request->code)->first();

            if (!$discount) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid discount code'
                ]);
            }

            if (!$discount->canBeUsed()) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Discount code is not valid or has expired'
                ]);
            }

            $discountAmount = 0;
            if ($request->amount) {
                $discountAmount = $discount->applyDiscount($request->amount);
                $discountAmount = $request->amount - $discountAmount;
            }

            return response()->json([
                'valid' => true,
                'discount' => [
                    'id' => $discount->id,
                    'code' => $discount->code,
                    'description' => $discount->description,
                    'type' => $discount->discount_type,
                    'value' => $discount->discount_value,
                    'max_discount_amount' => $discount->max_discount_amount,
                    'calculated_discount' => $discountAmount,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to validate discount code', [
                'error' => $e->getMessage(),
                'code' => $request->code,
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'valid' => false,
                'message' => 'Failed to validate discount code'
            ], 500);
        }
    }
}