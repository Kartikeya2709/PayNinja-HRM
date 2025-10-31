<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Discount extends Model
{
    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'valid_from',
        'valid_until',
        'usage_limit',
        'usage_count',
        'applicable_packages',
        'is_active'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'usage_limit' => 'int',
        'usage_count' => 'int',
        'applicable_packages' => 'array',
        'is_active' => 'boolean',
    ];

    public function invoices()
    {
        return $this->hasMany(CompanyPackageInvoice::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid(Builder $query)
    {
        return $query->where(function ($q) {
            $q->whereNull('valid_from')
              ->orWhere('valid_from', '<=', now());
        })->where(function ($q) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>=', now());
        });
    }

    public function applyDiscount($amount)
    {
        if (!$this->canBeUsed()) {
            return $amount;
        }

        $discount = 0;

        if ($this->discount_type === 'percentage') {
            $discount = ($amount * $this->discount_value) / 100;
        } elseif ($this->discount_type === 'fixed_amount') {
            $discount = $this->discount_value;
        }

        if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
            $discount = $this->max_discount_amount;
        }

        return $amount - $discount;
    }

    public function canBeUsed()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->valid_from && now()->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && now()->gt($this->valid_until)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}