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
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->onDelete('cascade');
            $table->enum('type', ['earning', 'deduction', 'reimbursement', 'statutory_contribution', 'loan_repayment', 'bonus', 'overtime']);
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->boolean('is_taxable')->default(false); // Primarily for earnings
            $table->nullableMorphs('related'); // For linking to specific leave, reimbursement, loan, etc.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
    }
};
