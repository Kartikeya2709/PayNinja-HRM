<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    protected $fillable = ['user_id', 'dob', 'gender', 'emergency_contact', 'joining_date', 'employment_type'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
