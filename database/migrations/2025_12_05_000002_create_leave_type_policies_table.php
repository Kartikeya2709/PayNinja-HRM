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
        Schema::create('leave_type_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_leave_policy_id')->constrained('company_leave_policies')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            $table->integer('allocated_days')->comment('Days allocated for this leave type in this policy');
            $table->integer('min_days')->default(0)->comment('Minimum days required for this leave type');
            $table->boolean('is_active')->default(true)->comment('Whether this leave type is active in this policy');
            $table->timestamps();
            $table->softDeletes();

            // Ensure each leave type is linked only once per policy
            $table->unique(['company_leave_policy_id', 'leave_type_id'], 'ltp_policy_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_type_policies');
    }
};
