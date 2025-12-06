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
        Schema::create('company_leave_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
            $table->string('name')->comment('Name of the leave policy');
            $table->text('description')->nullable()->comment('Description of the policy');
            $table->boolean('is_active')->default(true)->comment('Whether the policy is active');
            $table->timestamps();
            $table->softDeletes();

            // Ensure only one active policy per company per financial year
            $table->unique(['company_id', 'financial_year_id', 'is_active'], 'clp_company_fy_active_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_leave_policies');
    }
};
