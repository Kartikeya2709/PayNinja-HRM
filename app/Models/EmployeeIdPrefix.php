<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeIdPrefix extends Model
{
    use HasFactory;

    protected $table = 'employee_id_prefixes';

    protected $fillable = [
        'prefix',
        'padding',
        'start',
        'company_id',
        'employment_type_id',
        'is_common',
    ];

    protected $casts = [
        'is_common' => 'boolean',
    ];

    /**
     * Relationship with Company
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship with EmploymentType
     */
    public function employmentType()
    {
        return $this->belongsTo(EmploymentType::class, 'employment_type_id');
    }

    /**
     * Scope to filter by company
     */
    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter common prefixes
     */
    public function scopeCommon($query)
    {
        return $query->where('is_common', true);
    }

    /**
     * Scope to filter type-specific prefixes
     */
    public function scopeTypeSpecific($query)
    {
        return $query->where('is_common', false);
    }
}
