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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Add working_days column after total_days
            $table->json('working_days')
                ->nullable()
                ->after('total_days')
                ->comment('Array of working day dates in Y-m-d format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('total_working_days');
        });
    }
};
