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
        \DB::statement("ALTER TABLE `attendances` MODIFY COLUMN `status` ENUM('Present', 'Absent', 'Late', 'On Leave', 'Half Day', 'Holiday', 'Week-Off') NOT NULL DEFAULT 'Absent'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any existing 'Holiday' or 'Week-Off' statuses to 'On Leave'
        \DB::table('attendances')
            ->whereIn('status', ['Holiday', 'Week-Off'])
            ->update(['status' => 'On Leave']);
            
        // Then modify the column to remove the new statuses
        \DB::statement("ALTER TABLE `attendances` MODIFY COLUMN `status` ENUM('Present', 'Absent', 'Late', 'On Leave', 'Half Day') NOT NULL DEFAULT 'Absent'");
    }
};
