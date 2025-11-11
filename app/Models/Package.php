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
        'modules',
        'is_active'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
        'modules' => 'array',
    ];

    // Keep for backward compatibility, but modules are now stored in JSON
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
        // Return modules from JSON column with true/false values
        if ($this->modules && is_array($this->modules)) {
            return collect($this->modules)->filter(function ($enabled) {
                return $enabled === true;
            })->keys()->map(function ($moduleSlug) {
                return (object) ['name' => $moduleSlug, 'has_access' => true];
            })->values();
        }

        // Fallback to old relationship method
        return $this->packageModules()->where('has_access', true)->get();
    }

    public function hasModule($moduleSlug)
    {
        if ($this->modules && is_array($this->modules)) {
            return isset($this->modules[$moduleSlug]) && $this->modules[$moduleSlug] === true;
        }

        // Fallback to old relationship method
        return $this->packageModules()->where('module_name', $moduleSlug)->where('has_access', true)->exists();
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}