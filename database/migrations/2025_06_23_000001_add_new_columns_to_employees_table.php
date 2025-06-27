<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('parent_name')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('official_email')->nullable();
            $table->string('current_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('location')->nullable();
            $table->string('probation_period')->nullable();
            $table->string('reporting_manager_id')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('nominee_details')->nullable();
            $table->string('emergency_contact_relation')->nullable();
            $table->string('emergency_contact_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'parent_name',
                'marital_status',
                'personal_email',
                'official_email',
                'current_address',
                'permanent_address',
                'location',
                'probation_period',
                'reporting_manager',
                'blood_group',
                'nominee_details',
                'emergency_contact_relation',
                'emergency_contact_name',
            ]);
        });
    }
};
