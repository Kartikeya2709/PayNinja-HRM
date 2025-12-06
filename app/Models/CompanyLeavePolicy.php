<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyLeavePolicy extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'company_leave_policies';

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns this leave policy.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the financial year associated with this policy.
     */
    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class);
    }

    /**
     * Get the leave type policies associated with this policy.
     */
    public function leaveTypePolicies()
    {
        return $this->hasMany(LeaveTypePolicy::class);
    }

    /**
     * Get all leave types in this policy.
     */
    public function leaveTypes()
    {
        return $this->belongsToMany(
            LeaveType::class,
            'leave_type_policies',
            'company_leave_policy_id',
            'leave_type_id'
        )->withPivot('allocated_days', 'min_days', 'is_active');
    }

    /**
     * Scope to get active policies only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by financial year.
     */
    public function scopeForFinancialYear($query, $financialYearId)
    {
        return $query->where('financial_year_id', $financialYearId);
    }

    /**
     * Scope to filter by company.
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
