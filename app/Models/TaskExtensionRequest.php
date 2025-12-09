<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskExtensionRequest extends Model
{
    use HasFactory;

    protected $table = 'task_extension_requests';

    protected $fillable = [
        'task_id',
        'requested_by',
        'current_due_date',
        'requested_due_date',
        'reason',
        'status',
        'approved_by',
        'approval_comment',
    ];

    protected $casts = [
        'current_due_date' => 'datetime',
        'requested_due_date' => 'datetime',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
