<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    protected $fillable = ['name', 'domain', 'email', 'phone', 'address', 'created_by', 'is_active'];
    // protected $fillable = ['name', 'domain', 'email', 'phone', 'address', 'logo', 'created_by'];

    protected static function booted()
    {
        static::addGlobalScope('active', function ($query) {
            $query->where('companies.is_active', true);
        });
    }

    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'created_by');
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function designations()
    {
        return $this->hasMany(Designation::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Get the beneficiary badges defined for this company.
     */
    public function beneficiaryBadges()
    {
        return $this->hasMany(BeneficiaryBadge::class);
    }

    /**
     * Get the documents uploaded for this company
     */
    public function documents()
    {
        return $this->hasMany(CompanyDocument::class);
    }
}
