<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollRecordsTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('gross_salary', 12, 2);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('reimbursements', 12, 2)->default(0);
            $table->decimal('leave_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2);
            $table->enum('status', ['draft', 'processing', 'completed', 'cancelled'])->default('draft');
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_records');
    }
}
