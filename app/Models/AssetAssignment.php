<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'asset_id',
        'employee_id',
        'assigned_by',
        'assigned_date',
        'expected_return_date',
        'returned_date',
        'condition_on_assignment',
        'condition_on_return',
        'notes'
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'expected_return_date' => 'date',
        'returned_date' => 'date'
    ];

    public function asset()
    {
        // return $this->belongsTo(Asset::class);
          return $this->belongsTo(\App\Models\Asset::class);
    }

    public function employee()
    {
        // return $this->belongsTo(Employee::class);
         return $this->belongsTo(\App\Models\Employee::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isActive()
    {
        return is_null($this->returned_date);
    }
}
