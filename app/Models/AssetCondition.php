<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCondition extends Model
{
    protected $fillable = [
        'asset_id',
        'condition',
        'notes',
        'reported_by'
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
