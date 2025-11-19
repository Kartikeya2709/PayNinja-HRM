<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable , HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'company_id',
    ];
    
    /**
     * Get the user's role name.
     */
    public function getRoleNameAttribute()
    {
        // First check if we have a role_id relationship
        if ($this->role_id && $this->roleModel()) {
            return $this->roleModel()->first()->name;
        }
        // Fallback to the old role string
        return $this->role ?? 'No Role';
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        // If we have a roleModel, check against it first
        if ($this->role_id && $this->roleModel()) {
            $roleModel = $this->roleModel()->first();
            if ($roleModel) {
                if (is_array($role)) {
                    return in_array($roleModel->name, $role);
                }
                return $roleModel->name === $role;
            }
        }
        
        // Fallback to the old role string
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            // Find or create the role
            $roleModel = Role::firstOrCreate(
                ['name' => $role, 'company_id' => $this->company_id],
                ['permissions' => null] // null permissions as requested
            );
            $this->role_id = $roleModel->id;
            $this->save();
        } elseif ($role instanceof Role) {
            $this->role_id = $role->id;
            $this->save();
        }
        return $this;
    }

    /**
     * Remove role from the user.
     */
    public function removeRole()
    {
        $this->role_id = null;
        $this->save();
        return $this;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function employeeDetail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    /**
     * Get the employee record associated with the user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get the company that the user belongs to.
     * This uses the direct company relationship
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    /**
     * Get the company through employee relationship
     */
    public function employeeCompany()
    {
        return $this->hasOneThrough(
            Company::class,
            Employee::class,
            'user_id', // Foreign key on employees table
            'id', // Foreign key on companies table
            'id', // Local key on users table
            'company_id' // Local key on employees table
        );
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'company_id', 'company_id');
    }

    /**
     * Get the role associated with the user.
     */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the role associated with the user (alias for roleModel).
     */
    public function getRoleModelAttribute()
    {
        return $this->roleModel;
    }

}
