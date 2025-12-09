<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_extension_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('requested_by'); // Employee who requested extension
            $table->dateTime('current_due_date'); // Original due date
            $table->dateTime('requested_due_date'); // Requested new due date
            $table->text('reason'); // Reason for extension
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable(); // Who approved/rejected
            $table->text('approval_comment')->nullable(); // Comment from approver
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
            
            $table->index('task_id');
            $table->index('status');
            $table->index('requested_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_extension_requests');
    }
};
