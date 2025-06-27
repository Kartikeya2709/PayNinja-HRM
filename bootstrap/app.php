<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        Commands\AttendanceMasterScheduler::class,
        // Keep other commands registered if needed
        Commands\MarkAbsentEmployees::class,
        Commands\MarkLeavesCommand::class,
        Commands\MarkWeekendAsWeekoff::class,
        Commands\MarkHolidayAttendance::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'superadmin' => \App\Http\Middleware\superadmin::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // Master attendance scheduler - runs all attendance commands in order
        $schedule->command('attendance:run-all')
            ->dailyAt('19:00')  // Run daily at 1:00 AM
            // ->everyMinute()
            ->timezone('Asia/Kolkata')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/attendance.log'))
            ->description('Run all attendance marking commands in order');
            
        // For testing, you can uncomment this to run every minute
        // $schedule->command('attendance:run-all')
        //     ->everyMinute()
        //     ->timezone('Asia/Kolkata')
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path('logs/attendance.log'));



        // $schedule->command('attendance:mark-leaves')
        //     // ->dailyAt('19:00')
        //     ->everyMinute()
        //     ->timezone('Asia/Kolkata')
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path('logs/schedule.log'));

        // $schedule->command('attendance:mark-absent')
        //     ->dailyAt('00:05')  // Run at 12:05 AM
        //     ->timezone('Asia/Kolkata')
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path('logs/schedule.log'));
            
        // // Mark weekends as weekoff - run daily at 12:10 AM
        // $schedule->command('attendance:mark-weekend --date=tomorrow')
        //     ->dailyAt('00:10')
        //     ->timezone('Asia/Kolkata')
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path('logs/schedule.log'));
    })
    ->create();
