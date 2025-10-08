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

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPendingApproval()
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved()
    {
        return $this->approval_status === 'approved';
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */
    public function startVisit()
    {
        $this->update([
            'status' => 'in_progress',
            'actual_start_datetime' => now(),
        ]);
    }

    // public function completeVisit(array $data)
    // {
    //     $this->update([
    //         'status' => 'completed',
    //         'actual_end_datetime' => now(),
    //         'visit_notes' => $data['visit_notes'] ?? null,
    //         'visit_attachments' => $data['visit_attachments'] ?? null,
    //     ]);
    // }


    public function completeVisit(array $data)
    {
        $this->update([
            'status' => 'completed',
            'actual_end_datetime' => now(),
            'visit_notes' => $data['visit_notes'] ?? null,
            'visit_attachments' => $data['visit_attachments'] ?? null,
            'latitude' => $data['latitude'] ?? $this->latitude,
            'longitude' => $data['longitude'] ?? $this->longitude,
        ]);
    }

    public function approve(Employee $manager)
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $manager->id,
        ]);
    }

    public function reject(Employee $manager)
    {
        $this->update([
            'approval_status' => 'rejected',
            'approved_at' => now(),
            'approved_by' => $manager->id,
        ]);
    }
}
