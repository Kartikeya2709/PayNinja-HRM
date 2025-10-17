<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemoRequest extends Model
{
    use SoftDeletes;   

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'company_name',
        'company_size',
        'additional_info',
    ];
}
