<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('assigned_by')->nullable(); // user id
            $table->unsignedBigInteger('assigned_to')->nullable(); // employee id
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('team_lead_id')->nullable()->after('assigned_by');
            $table->foreign('team_lead_id')->references('id')->on('employees')->onDelete('set null');
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('open'); // open, in_progress, completed, closed
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['company_id']);
            $table->index(['assigned_to']);

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
