<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    protected $fillable = ['name', 'domain', 'email', 'phone', 'address', 'created_by'];
    // protected $fillable = ['name', 'domain', 'email', 'phone', 'address', 'logo', 'created_by'];

    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'created_by');
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
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
