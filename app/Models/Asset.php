<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'name',
        'asset_code',
        'description',
        'category_id',
        'company_id',
        'purchase_cost',
        'purchase_date',
        'status',
        'condition',
        'notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_cost' => 'decimal:2'
    ];

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function currentAssignment()
    {
        // return $this->hasOne(AssetAssignment::class)->whereNull('returned_date')->latest();
        return $this->hasOne(\App\Models\AssetAssignment::class)
        ->whereNull('returned_date') // only active assignment
        ->latestOfMany();
    }

    public function conditions()
    {
        return $this->hasMany(AssetCondition::class);
    }

    public function isAvailable()
    {
        return $this->status === 'available';
    }

    public function isAssigned()
    {
        return $this->status === 'assigned';
    }
}
