<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'default_days',
        'requires_attachment',
        'is_active',
        'company_id',
        'monthly_limit',
        'yearly_limit',
        'disbursement_cycle',
        'disbursement_time',
        'enable_carry_forward',
        'max_carry_forward_days',
        'allow_carry_forward_to_next_year',
        'yearly_carry_forward_limit',
        'allow_half_day_leave',
        'allow_negative_balance',
        'half_day_deduction_priority',
        'leave_value_per_cycle'
    ];

    protected $casts = [
        'requires_attachment' => 'boolean',
        'is_active' => 'boolean',
        'enable_carry_forward' => 'boolean',
        'allow_carry_forward_to_next_year' => 'boolean',
        'allow_half_day_leave' => 'boolean',
        'allow_negative_balance' => 'boolean',
    ];

    /**
     * Get the company that owns the leave type.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the leave balances for this leave type.
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the leave requests for this leave type.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the leave type policies for this leave type.
     */
    public function leaveTypePolicies()
    {
        return $this->hasMany(LeaveTypePolicy::class);
    }

    /**
     * Get the company leave policies that contain this leave type.
     */
    public function companyleavePolicies()
    {
        return $this->belongsToMany(
            CompanyLeavePolicy::class,
            'leave_type_policies',
            'leave_type_id',
            'company_leave_policy_id'
        )->withPivot('allocated_days', 'min_days', 'is_active');
    }
}
