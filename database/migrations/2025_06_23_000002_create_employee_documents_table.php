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
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('type'); // e.g. aadhaar_card, pan_card, etc.
            $table->json('file_path'); // store multiple file paths as JSON
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
