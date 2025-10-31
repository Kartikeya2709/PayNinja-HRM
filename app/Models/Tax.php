<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Tax extends Model
{
    protected $fillable = [
        'name',
        'rate',
        'country',
        'state',
        'is_active'
    ];

    protected $casts = [
        'rate' => 'decimal:4',
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

    public function scopeForCountry(Builder $query, $country)
    {
        return $query->where('country', $country);
    }

    public function scopeForState(Builder $query, $state)
    {
        return $query->where('state', $state);
    }
}