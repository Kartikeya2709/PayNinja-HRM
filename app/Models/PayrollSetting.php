<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Company;

class PayrollSetting extends Model
{
    use HasFactory;

    protected $table = 'payroll_settings';

    protected $fillable = [
        'company_id',
        'deductible_leave_type_ids',
        'late_arrival_threshold',
        'late_arrival_deduction_days',
        'days_in_month',
        'enable_halfday_deduction',
        'enable_reimbursement',
    ];
    
    protected $casts = [
        'enable_halfday_deduction' => 'boolean',
        'enable_reimbursement' => 'boolean',
        'deductible_leave_type_ids' => 'array',
    ];

    /**
     * Get the company that owns the payroll setting.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}