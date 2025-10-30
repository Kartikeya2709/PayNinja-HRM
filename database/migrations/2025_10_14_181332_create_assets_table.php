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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained('asset_categories');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->decimal('purchase_cost', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('status')->default('available'); // available, assigned, maintenance, retired
            $table->string('condition')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
