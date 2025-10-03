<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Department;
class Designation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'department_id',
        'title',
        'description',
        'level'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
    public function department()
    {
    return $this->belongsTo(Department::class, 'department_id');
    }

      // 👇 Hide the full department relation from API output
    protected $hidden = ['department'];

    // 👇 Always include department_name in API output
    protected $appends = ['department_name'];

    // Accessor for department_name
    public function getDepartmentNameAttribute()
    {
        return $this->department ? $this->department->name : null;
    }
   

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
}
