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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');//
            $table->foreignId('designation_id')->constrained()->onDelete('cascade');//
            $table->string('name');//
            $table->string('email')->unique();//
            $table->string('phone', 15);//
            $table->date('dob');//
            $table->enum('gender', ['male', 'female', 'other']);//
            $table->string('emergency_contact', 15);//
            $table->date('joining_date');//
            $table->enum('employment_type', ['permanent', 'contract', 'intern']);//
            // $table->text('address');
            $table->string('avatar')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
