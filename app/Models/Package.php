<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'name',
        'description',
        'pricing_type',
        'base_price',
        'currency',
        'billing_cycle',
        'is_active'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function packageModules()
    {
        return $this->hasMany(PackageModule::class);
    }

    public function companyPackages()
    {
        return $this->hasMany(CompanyPackage::class);
    }

    public function pricingTiers()
    {
        return $this->hasMany(PackagePricingTier::class);
    }

    public function getActiveModules()
    {
        return $this->packageModules()->where('has_access', true)->get();
    }

    public function getPriceForUsers($userCount)
    {
        $tier = $this->pricingTiers()
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

        return $tier ? $tier->price : $this->base_price;
    }

    public function getTotalPrice($userCount, $discount = null, $tax = null)
    {
        $pricingService = app(\App\Services\PricingService::class);

        $result = $pricingService->calculatePackagePrice($this, $userCount, $discount);

        return $result['total'];
    }
}