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
        Schema::create('handbook_acknowledgments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('handbook_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('acknowledged_at');
            $table->timestamps();

            $table->foreign('handbook_id')->references('id')->on('handbooks')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['handbook_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handbook_acknowledgments');
    }
};
