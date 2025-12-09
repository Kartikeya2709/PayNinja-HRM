<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use App\Models\User;
use App\Models\Employee;
use App\Models\Designation;
use App\Models\Department;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'assigned_by',
        'assigned_to',
        'team_lead_id',
        'title',
        'description',
        'priority',
        'status',
        'due_at',
        'completed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function teamLead()
    {
        return $this->belongsTo(Employee::class, 'team_lead_id');
    }

    public function extensionRequests()
    {
        return $this->hasMany(TaskExtensionRequest::class);
    }

    public function pendingExtensionRequest()
    {
        return $this->hasOne(TaskExtensionRequest::class)->where('status', 'pending')->latest();
    }

    // Backwards-compatible single assignee (if used elsewhere)
    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    // Multiple assignees via pivot table
    public function assignedToMany()
    {
        return $this->belongsToMany(Employee::class, 'task_employee', 'task_id', 'employee_id')->withTimestamps();
    }

    public function designations()
    {
        return $this->belongsToMany(Designation::class, 'task_designation', 'task_id', 'designation_id')->withTimestamps();
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'task_department', 'task_id', 'department_id')->withTimestamps();
    }

    public function exemptions()
    {
        return $this->belongsToMany(Employee::class, 'task_exemption', 'task_id', 'employee_id')->withTimestamps();
    }

    /**
     * Resolve all assignee employee IDs from explicit assignees, designations and departments.
     * Excludes any employees listed in exemptions.
     *
     * @return \Illuminate\Support\Collection  Collection of employee IDs
     */
    public function resolveAssigneeIds()
    {
        $ids = collect();

        // explicit assigned employees via pivot
        $ids = $ids->merge($this->assignedToMany()->pluck('employees.id'));

        // employees via designations
        $designationIds = $this->designations()->pluck('designations.id');
        if ($designationIds->isNotEmpty()) {
            $ids = $ids->merge(Employee::whereIn('designation_id', $designationIds)->pluck('id'));
        }

        // employees via departments
        $departmentIds = $this->departments()->pluck('departments.id');
        if ($departmentIds->isNotEmpty()) {
            $ids = $ids->merge(Employee::whereIn('department_id', $departmentIds)->pluck('id'));
        }

        // Remove exempted employees
        $exemptedIds = $this->exemptions()->pluck('employees.id');
        if ($exemptedIds->isNotEmpty()) {
            $ids = $ids->diff($exemptedIds);
        }

        return $ids->filter()->unique()->values();
    }

    /**
     * Check if task is overdue (due_at is in the past and not completed)
     */
    public function isOverdue()
    {
        if ($this->completed_at || $this->status === 'completed') {
            return false;
        }

        if (!$this->due_at) {
            return false;
        }

        return Carbon::now()->gt($this->due_at);
    }

    /**
     * Get days until due or days overdue
     */
    public function daysUntilDue()
    {
        if (!$this->due_at) {
            return null;
        }

        return now()->diffInDays($this->due_at, false);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
