<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'permissions', 'company_id', 'is_default', 'is_active'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
