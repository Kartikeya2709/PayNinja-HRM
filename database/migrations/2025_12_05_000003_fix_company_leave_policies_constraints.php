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
        // Drop the existing tables if they exist
        Schema::dropIfExists('leave_type_policies');
        Schema::dropIfExists('company_leave_policies');

        // Recreate company_leave_policies with corrected constraint name
        Schema::create('company_leave_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('financial_year_id')->constrained('financial_years')->onDelete('cascade');
            $table->string('name')->comment('Name of the leave policy');
            $table->text('description')->nullable()->comment('Description of the policy');
            $table->boolean('is_active')->default(true)->comment('Whether the policy is active');
            $table->timestamps();
            $table->softDeletes();

            // Use shorter constraint name to avoid MySQL identifier length limit
            $table->unique(['company_id', 'financial_year_id', 'is_active'], 'clp_company_fy_active_unique');
        });

        // Recreate leave_type_policies with corrected constraint name
        Schema::create('leave_type_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_leave_policy_id')->constrained('company_leave_policies')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            $table->integer('allocated_days')->comment('Days allocated for this leave type in this policy');
            $table->integer('min_days')->default(0)->comment('Minimum days required for this leave type');
            $table->boolean('is_active')->default(true)->comment('Whether this leave type is active in this policy');
            $table->timestamps();
            $table->softDeletes();

            // Use shorter constraint name to avoid MySQL identifier length limit
            $table->unique(['company_leave_policy_id', 'leave_type_id'], 'ltp_policy_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_type_policies');
        Schema::dropIfExists('company_leave_policies');
    }
};
