<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalary extends Model
{
    use SoftDeletes;

    protected $table = 'employee_salaries';

    protected $fillable = [
        'ctc',
        'pan_number',
        'employee_id',
        'basic_salary',
        'hra',
        'da',
        'other_allowances',
        'gross_salary',
        'pf_deduction',
        'esi_deduction',
        'tds_deduction',
        'professional_tax',
        'loan_deductions',
        'total_deductions',
        'net_salary',
        'currency',
        'payment_method',
        'payment_frequency',
        'bank_name',
        'account_number',
        'ifsc_code',
        'status',
        'effective_from',
        'effective_to',
        'notes',
        'is_current',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'da' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'pf_deduction' => 'decimal:2',
        'esi_deduction' => 'decimal:2',
        'tds_deduction' => 'decimal:2',
        'professional_tax' => 'decimal:2',
        'loan_deductions' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_current' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'effective_from',
        'effective_to',
        'start_date',
        'end_date',
        'paid_at',
        'approved_at',
        'deleted_at',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new salary record, ensure only one is marked as current
        static::creating(function ($model) {
            if ($model->is_current) {
                static::where('employee_id', $model->employee_id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }
        });

        // When updating a salary record, ensure only one is marked as current
        static::updating(function ($model) {
            if ($model->isDirty('is_current') && $model->is_current) {
                static::where('employee_id', $model->employee_id)
                    ->where('id', '!=', $model->id)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);
            }
        });
    }

    /**
     * Get the employee that owns the salary record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who approved the salary record.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
