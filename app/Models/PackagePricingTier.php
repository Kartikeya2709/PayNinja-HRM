<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackagePricingTier extends Model
{
    protected $fillable = [
        'package_id',
        'tier_name',
        'min_users',
        'max_users',
        'price',
        'currency',
        'is_active'
    ];

    protected $casts = [
        'min_users' => 'int',
        'max_users' => 'int',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}