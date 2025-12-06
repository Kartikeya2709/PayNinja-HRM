<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveTypePolicy extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_type_policies';

    protected $fillable = [
        'company_leave_policy_id',
        'leave_type_id',
        'allocated_days',
        'min_days',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the company leave policy that owns this leave type policy.
     */
    public function companyLeavePolicy()
    {
        return $this->belongsTo(CompanyLeavePolicy::class);
    }

    /**
     * Get the leave type associated with this policy.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Scope to get active leave type policies only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
