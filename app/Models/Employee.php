<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'company_id',
        'department_id',
        'designation_id',
        'name',
        'email',
        'phone',
        'dob',
        'gender',
        'emergency_contact',
        'joining_date',
        'employment_type',
        'address',
        'avatar',
        'created_by',
        'updated_by',
        'status'
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function employeeDetail()
    {
        return $this->hasOne(EmployeeDetail::class, 'user_id', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    // Helper methods
    public function isReporterInTeam(Team $team)
    {
        return $this->teams()
                    ->wherePivot('team_id', $team->id)
                    ->wherePivot('role', 'reporter')
                    ->exists();
    }

    public function isReporteeInTeam(Team $team)
    {
        return $this->teams()
                    ->wherePivot('team_id', $team->id)
                    ->wherePivot('role', 'reportee')
                    ->exists();
    }

    /**
     * Get the leave balances for this employee.
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Get the leave requests for this employee.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the leave balance for a specific leave type and year.
     */
    public function getLeaveBalance($leaveTypeId, $year = null)
    {
        $year = $year ?? Carbon::now()->year;
        
        return $this->leaveBalances()
                    ->where('leave_type_id', $leaveTypeId)
                    ->where('year', $year)
                    ->first();
    }

    /**
     * Check if employee has sufficient leave balance.
     */
    public function hasSufficientLeaveBalance($leaveTypeId, $days, $year = null)
    {
        $balance = $this->getLeaveBalance($leaveTypeId, $year);
        
        if (!$balance) {
            return false;
        }
        
        return ($balance->total_days - $balance->used_days) >= $days;
    }
}
