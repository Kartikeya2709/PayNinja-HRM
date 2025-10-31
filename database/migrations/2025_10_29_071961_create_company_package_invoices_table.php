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
        Schema::create('company_package_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_package_id')->constrained('company_packages')->onDelete('cascade');
            $table->string('invoice_number', 50)->unique();
            $table->date('billing_period_start');
            $table->date('billing_period_end');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 3);
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('discount_id')->nullable()->constrained('discounts')->onDelete('set null');
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_package_invoices');
    }
};