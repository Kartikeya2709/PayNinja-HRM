<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('designations', function (Blueprint $table) {
            // Add department_id column
            $table->unsignedBigInteger('department_id')->nullable()->after('id'); // adjust position if needed

            // Add foreign key constraint
            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->onDelete('set null'); // or 'cascade' if you want
        });
    }

    public function down()
    {
        Schema::table('designations', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['department_id']);

            // Drop the column
            $table->dropColumn('department_id');
        });
    }
};
