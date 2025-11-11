<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Discount;
use App\Models\Tax;
use Illuminate\Support\Facades\Log;

class PricingService
{
    /**
     * Calculate total package price with tiers, discounts, taxes
     */
    public function calculatePackagePrice(Package $package, int $userCount, ?string $discountCode = null): array
    {
        $subtotal = $this->calculateTieredPrice($package, $userCount);
        $discount = null;
        $discountAmount = 0;
        $tax = null;
        $taxAmount = 0;

        if ($discountCode) {
            $discount = $this->validateDiscountCode($discountCode, $package->id);
            if ($discount) {
                $discountAmount = $this->applyDiscount($subtotal, $discount);
            }
        }

        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // Assume default tax for now - in real app, get based on company location
        $tax = Tax::active()->first();
        if ($tax) {
            $taxAmount = $this->calculateTax($subtotalAfterDiscount, $tax);
        }

        $total = $subtotalAfterDiscount + $taxAmount;

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'total' => round($total, 2),
            'currency' => $package->currency,
            'discount' => $discount,
            'tax' => $tax,
        ];
    }

    /**
     * Handle tiered pricing logic
     */
    public function calculateTieredPrice(Package $package, int $userCount): float
    {
        $tier = $package->pricingTiers()
            ->where('is_active', true)
            ->where(function ($query) use ($userCount) {
                $query->where('min_users', '<=', $userCount)
                      ->orWhereNull('min_users');
            })
            ->where(function ($query) use ($userCount) {
                $query->where('max_users', '>=', $userCount)
                      ->orWhereNull('max_users');
            })
            ->orderBy('min_users', 'desc')
            ->first();

        return $tier ? $tier->price : $package->base_price;
    }

    /**
     * Apply percentage or fixed amount discounts
     */
    public function applyDiscount(float $subtotal, Discount $discount): float
    {
        if (!$discount->canBeUsed()) {
            return 0;
        }

        $discountAmount = 0;

        if ($discount->discount_type === 'percentage') {
            $discountAmount = ($subtotal * $discount->discount_value) / 100;
        } elseif ($discount->discount_type === 'fixed_amount') {
            $discountAmount = $discount->discount_value;
        }

        if ($discount->max_discount_amount && $discountAmount > $discount->max_discount_amount) {
            $discountAmount = $discount->max_discount_amount;
        }

        return round($discountAmount, 2);
    }

    /**
     * Calculate tax amounts
     */
    public function calculateTax(float $subtotal, Tax $tax): float
    {
        $taxAmount = ($subtotal * $tax->rate) / 100;
        return round($taxAmount, 2);
    }

    /**
     * Validate discount codes
     */
    public function validateDiscountCode(string $code, ?int $packageId = null): ?Discount
    {
        $discount = Discount::where('code', $code)->active()->valid()->first();

        if (!$discount) {
            return null;
        }

        // Check if discount is applicable to specific packages
        if ($discount->applicable_packages && $packageId) {
            $applicablePackages = $discount->applicable_packages;
            if (!in_array($packageId, $applicablePackages)) {
                return null;
            }
        }

        return $discount;
    }
}