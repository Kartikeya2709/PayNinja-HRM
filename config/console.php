<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Console Commands
    |--------------------------------------------------------------------------
    |
    | Here you may register all of the console commands for your application.
    | These commands can be called from the command line when running the
    | application using the Artisan CLI.
    |
    */

    'commands' => [
        // Register the leave disbursement command
        \App\Console\Commands\ProcessLeaveDisbursement::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Command Scheduling
    |--------------------------------------------------------------------------
    |
    | Here you may define the schedule for when console commands should run.
    | This schedule is used by the command scheduler to determine when
    | each command should be executed.
    |
    */

    'schedule' => [
        // Schedule the leave disbursement command to run daily
        \App\Console\Commands\ProcessLeaveDisbursement::class => [
            'description' => 'Process leave disbursement for all employees',
            'schedule' => 'daily',
            'timezone' => 'UTC',
        ],
    ],

];
