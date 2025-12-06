<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLeaveSettingsToLeaveTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            // Monthly and yearly limits
            $table->integer('monthly_limit')->nullable()->after('default_days');
            $table->integer('yearly_limit')->nullable()->after('monthly_limit');

            // Disbursement cycle settings
            $table->enum('disbursement_cycle', ['monthly', 'quarterly', 'half_yearly', 'yearly'])
                  ->default('yearly')
                  ->after('yearly_limit');

            $table->enum('disbursement_time', ['start_of_cycle', 'end_of_cycle'])
                  ->default('start_of_cycle')
                  ->after('disbursement_cycle');

            // Carry forward settings
            $table->boolean('enable_carry_forward')->default(false)->after('disbursement_time');
            $table->integer('max_carry_forward_days')->nullable()->after('enable_carry_forward');
            $table->boolean('allow_carry_forward_to_next_year')->default(false)->after('max_carry_forward_days');
            $table->integer('yearly_carry_forward_limit')->default(0)->after('allow_carry_forward_to_next_year');

            // Half day settings
            $table->boolean('allow_half_day_leave')->default(false)->after('yearly_carry_forward_limit');
            $table->boolean('allow_negative_balance')->default(false)->after('allow_half_day_leave');
            $table->enum('half_day_deduction_priority', ['full_day_first', 'half_day_first'])
                  ->default('full_day_first')
                  ->after('allow_negative_balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn([
                'monthly_limit',
                'yearly_limit',
                'disbursement_cycle',
                'disbursement_time',
                'enable_carry_forward',
                'max_carry_forward_days',
                'allow_carry_forward_to_next_year',
                'yearly_carry_forward_limit',
                'allow_half_day_leave',
                'allow_negative_balance',
                'half_day_deduction_priority'
            ]);
        });
    }
}
