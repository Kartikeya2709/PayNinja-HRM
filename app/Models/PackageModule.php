<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageModule extends Model
{
    protected $fillable = [
        'package_id',
        'module_name',
        'has_access'
    ];

    protected $casts = [
        'has_access' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}