<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'auto_absent_time',
        'allow_multiple_check_in',
        'track_location',
        'weekend_days',
        'office_start_time',
        'office_end_time',
        'work_hours',
        'grace_period',
        'enable_geolocation',
        'office_latitude',
        'office_longitude',
        'geofence_radius',
        'created_by',
    ];

    protected $casts = [
        'auto_absent_time' => 'string',
        'allow_multiple_check_in' => 'boolean',
        'track_location' => 'boolean',
        'weekend_days' => 'array',
        'office_start_time' => 'string',
        'office_end_time' => 'string',
        // 'work_hours' => 'integer',
        'work_hours' => 'float', // Changed to float for decimal support
        'grace_period' => 'string',
        'enable_geolocation' => 'boolean',
        'office_latitude' => 'float',
        'office_longitude' => 'float',
        'geofence_radius' => 'integer',
    ];
    
    // Default values for the model
    protected $attributes = [
        'office_start_time' => '09:00:00',
        'office_end_time' => '18:00:00',
        'work_hours' => 8,
        'grace_period' => '00:15:00',
        'enable_geolocation' => false,
        'geofence_radius' => 100,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
