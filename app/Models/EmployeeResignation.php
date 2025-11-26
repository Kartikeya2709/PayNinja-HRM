<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class EmployeeResignation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'company_id',
        'resignation_type',
        'reason',
        'resignation_date',
        'last_working_date',
        'notice_period_days',
        'attachment_path',
        'status',
        'reporting_manager_id',
        'hr_admin_id',
        'employee_remarks',
        'manager_remarks',
        'hr_remarks',
        'admin_remarks',
        'exit_interview_completed',
        'exit_interview_date',
        'handover_completed',
        'handover_document_path',
        'assets_returned',
        'final_settlement_completed',
        'final_settlement_document_path',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'last_working_date' => 'date',
        'exit_interview_date' => 'date',
        'approved_at' => 'datetime',
        'exit_interview_completed' => 'boolean',
        'handover_completed' => 'boolean',
        'assets_returned' => 'boolean',
        'final_settlement_completed' => 'boolean',
    ];

    /**
     * Get the employee that owns the resignation request.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the company that owns the resignation request.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the reporting manager.
     */
    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    /**
     * Get the HR admin who processed the request.
     */
    public function hrAdmin()
    {
        return $this->belongsTo(User::class, 'hr_admin_id');
    }

    /**
     * Get the user who approved the request.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending resignations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved resignations.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected resignations.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include withdrawn resignations.
     */
    public function scopeWithdrawn($query)
    {
        return $query->where('status', 'withdrawn');
    }

    /**
     * Get the color for the status badge.
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'hr_approved' => 'info',
            'manager_approved' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'withdrawn' => 'secondary',
        ];

        return $colors[strtolower($this->status)] ?? 'primary';
    }

    /**
     * Calculate remaining days until last working date.
     *
     * @return int|null
     */
    public function getRemainingDaysAttribute()
    {
        if ($this->last_working_date) {
            // dd($this->last_working_date);
            return ceil(Carbon::now()->diffInDays(Carbon::parse($this->last_working_date), false));
        }
        return null;
    }

    /**
     * Check if resignation is active (not rejected or withdrawn).
     *
     * @return bool
     */
    public function isActive()
    {
        return in_array($this->status, ['pending', 'hr_approved', 'manager_approved', 'approved']);
    }

    /**
     * Check if resignation can be withdrawn.
     *
     * @return bool
     */
    public function canBeWithdrawn()
    {
        return in_array($this->status, ['pending', 'hr_approved']);
    }

    /**
     * Check if resignation requires exit process completion.
     *
     * @return bool
     */
    public function requiresExitProcess()
    {
        return $this->status === 'approved';
    }

    /**
     * Get the resignation type label.
     *
     * @return string
     */
    public function getResignationTypeLabelAttribute()
    {
        $labels = [
            'voluntary' => 'Voluntary Resignation',
            'involuntary' => 'Involuntary Termination',
            'retirement' => 'Retirement',
            'contract_end' => 'Contract End',
        ];

        return $labels[$this->resignation_type] ?? ucfirst($this->resignation_type);
    }

    /**
     * Get the status label.
     *
     * @return string
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending Approval',
            'hr_approved' => 'HR Approved',
            'manager_approved' => 'Manager Approved',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'withdrawn' => 'Withdrawn',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Get exit interview status.
     *
     * @return string
     */
    public function getExitInterviewStatusAttribute()
    {
        return $this->exit_interview_completed ? 'completed' : 'pending';
    }

    /**
     * Get handover status.
     *
     * @return string
     */
    public function getHandoverStatusAttribute()
    {
        return $this->handover_completed ? 'completed' : 'pending';
    }

    /**
     * Get assets status.
     *
     * @return string
     */
    public function getAssetsStatusAttribute()
    {
        return $this->assets_returned ? 'completed' : 'pending';
    }

    /**
     * Get settlement status.
     *
     * @return string
     */
    public function getSettlementStatusAttribute()
    {
        return $this->final_settlement_completed ? 'completed' : 'pending';
    }

    /**
     * Check if exit process is complete.
     *
     * @return bool
     */
    public function isExitProcessComplete()
    {
        return $this->exit_interview_completed &&
               $this->handover_completed &&
               $this->assets_returned &&
               $this->final_settlement_completed;
    }
}
