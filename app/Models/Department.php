<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Designation;

class Department extends Model
{
    protected $fillable = ['name', 'description', 'company_id'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }   

    public function designations()
    {
        return $this->hasMany(Designation::class, 'department_id');
    }

    protected $appends = ['department_name'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function getDepartmentNameAttribute()
    {
        return $this->department ? $this->department->name : null;
    }

   

    public function designations()
    {
        return $this->hasMany(Designation::class, 'department_id');
    }



         protected $appends = ['department_name'];

        public function department()
        {
            return $this->belongsTo(Department::class, 'department_id');
        }

        public function getDepartmentNameAttribute()
        {
            return $this->department ? $this->department->name : null;
        }
}
