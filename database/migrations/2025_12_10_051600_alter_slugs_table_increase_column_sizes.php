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
        Schema::table('slugs', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->string('slug', 100)->change();
            $table->string('icon', 100)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slugs', function (Blueprint $table) {
            $table->string('name', 50)->change();
            $table->string('slug', 50)->change();
            $table->string('icon', 50)->nullable()->change();
        });
    }
};
