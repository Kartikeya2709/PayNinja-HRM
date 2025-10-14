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
        Schema::table('employee_id_prefixes', function (Blueprint $table) {
            $table->foreignId('employment_type_id')->nullable()->after('company_id')->constrained('employment_types')->onDelete('cascade');
            $table->boolean('is_common')->default(false)->after('employment_type_id');
            $table->string('employment_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_id_prefixes', function (Blueprint $table) {
            $table->dropForeign(['employment_type_id']);
            $table->dropColumn(['employment_type_id', 'is_common']);
            $table->enum('employment_type', ['permanent', 'trainee'])->change();
        });
    }
};
