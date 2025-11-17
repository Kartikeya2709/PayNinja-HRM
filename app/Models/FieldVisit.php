<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FieldVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'reporting_manager_id',
        'visit_title',
        'visit_description',
        'location_name',
        'location_address',
        'latitude',
        'longitude',
        'current_location',
        'scheduled_start_datetime',
        'scheduled_end_datetime',
        'actual_start_datetime',
        'actual_end_datetime',
        'status',
        'visit_notes',
        'manager_feedback',
        'approval_status',
        'approved_at',
        'approved_by',
        'visit_attachments',
    ];

    protected $casts = [
        'visit_attachments' => 'array',
        'current_location' => 'string',
        'scheduled_start_datetime' => 'datetime',
        'scheduled_end_datetime' => 'datetime',
        'actual_start_datetime' => 'datetime',
        'actual_end_datetime' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */
    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isPendingApproval()
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected()
    {
        return $this->approval_status === 'rejected';
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function approve(Employee $manager)
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $manager->id,
            'status' => 'completed'
        ]);
    }

    public function reject(Employee $manager)
    {
        $this->update([
            'approval_status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => $manager->id,
            'status' => 'completed'
        ]);
    }
}
