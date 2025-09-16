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
        Schema::create('employee_resignations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            
            // Resignation details
            $table->enum('resignation_type', ['voluntary', 'involuntary', 'retirement', 'contract_end'])->default('voluntary');
            $table->text('reason');
            $table->date('resignation_date');
            $table->date('last_working_date');
            $table->integer('notice_period_days')->default(30);
            $table->string('attachment_path')->nullable();
            
            // Status workflow
            $table->enum('status', ['pending', 'hr_approved', 'manager_approved', 'approved', 'rejected', 'withdrawn'])->default('pending');
            
            // Approval chain
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('hr_admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Remarks and feedback
            $table->text('employee_remarks')->nullable();
            $table->text('manager_remarks')->nullable();
            $table->text('hr_remarks')->nullable();
            $table->text('admin_remarks')->nullable();
            
            // Exit process tracking
            $table->boolean('exit_interview_completed')->default(false);
            $table->date('exit_interview_date')->nullable();
            $table->boolean('handover_completed')->default(false);
            $table->string('handover_document_path')->nullable();
            $table->boolean('assets_returned')->default(false);
            $table->boolean('final_settlement_completed')->default(false);
            
            // System fields
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['employee_id', 'status']);
            $table->index(['company_id', 'status']);
            $table->index('resignation_date');
            $table->index('last_working_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_resignations');
    }
};