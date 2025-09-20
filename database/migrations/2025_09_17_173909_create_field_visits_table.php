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
        Schema::create('field_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('visit_title');
            $table->text('visit_description')->nullable();
            $table->string('location_name');
            $table->string('location_address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->dateTime('scheduled_start_datetime');
            $table->dateTime('scheduled_end_datetime');
            $table->dateTime('actual_start_datetime')->nullable();
            $table->dateTime('actual_end_datetime')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('visit_notes')->nullable();
            $table->text('manager_feedback')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->dateTime('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->json('visit_attachments')->nullable(); // Store photo URLs/paths
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_visits');
    }
};
