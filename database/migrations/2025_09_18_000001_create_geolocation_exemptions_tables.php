<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('department_geolocation_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_setting_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Ensure a department can only be exempted once per attendance setting
            $table->unique(['attendance_setting_id', 'department_id'], 'unique_department_exemption');
        });

        Schema::create('employee_geolocation_exemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_setting_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Ensure an employee can only be exempted once per attendance setting
            $table->unique(['attendance_setting_id', 'employee_id'], 'unique_employee_exemption');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_geolocation_exemptions');
        Schema::dropIfExists('department_geolocation_exemptions');
    }
};