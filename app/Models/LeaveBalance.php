<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'total_days',
        'used_days',
        'year',
        'carried_over_days',
        'remaining_days'
    ];

    /**
     * Get the employee that owns the leave balance.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the leave type for this balance.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the remaining balance.
     * Note: This accessor has been modified to return the actual stored remaining_days
     * value instead of recalculating it, to properly support carry forward days.
     */
    public function getRemainingDaysAttribute()
    {
        // Return the actual stored value to support carry forward logic
        return $this->attributes['remaining_days'] ?? ($this->total_days - $this->used_days);
    }
}
