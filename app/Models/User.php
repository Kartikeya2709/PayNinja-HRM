<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// Role management

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
        'company_id',
        'is_active',
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function ($query) {
            $query->where('users.is_active', true);
        });
    }
    
    /**
     * Get the user's role name.
     */
    public function getRoleNameAttribute()
    {
        return $this->role ?? 'No Role';
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole($role)
    {
        if (is_array($role)) {
            return in_array($this->role, $role);
        }
        return $this->role === $role;
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
        return $this->hasOne(EmployeeDetail::class)->withoutGlobalScopes();
    }

    /**
     * Get the employee record associated with the user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class)->withoutGlobalScopes();
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


}
