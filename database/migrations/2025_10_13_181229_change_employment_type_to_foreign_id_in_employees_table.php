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
        Schema::table('employees', function (Blueprint $table) {
            // Drop the existing enum column
            $table->dropColumn('employment_type');

            // Add the new foreign key column
            $table->foreignId('employment_type_id')->nullable()->constrained('employment_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop the foreign key constraint and column
            $table->dropForeign(['employment_type_id']);
            $table->dropColumn('employment_type_id');

            // Restore the original enum column
            $table->enum('employment_type', ['permanent', 'contract', 'intern']);
        });
    }
};
