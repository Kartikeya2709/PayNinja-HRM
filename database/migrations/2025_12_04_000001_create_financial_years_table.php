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
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name')->comment('Name of the financial year (e.g., FY 2024-2025)');
            $table->date('start_date')->comment('Start date of the financial year');
            $table->date('end_date')->comment('End date of the financial year');
            $table->boolean('is_active')->default(false)->comment('Whether this is the current active financial year');
            $table->boolean('is_locked')->default(false)->comment('Whether this financial year is locked and cannot be modified');
            $table->timestamps();

            // Ensure only one active financial year per company
            $table->unique(['company_id', 'is_active'], 'unique_active_financial_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};
