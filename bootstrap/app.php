<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        Commands\AttendanceMasterScheduler::class,
        // Keep other commands registered if needed
        Commands\MarkAbsentEmployees::class,
        Commands\MarkLeavesCommand::class,
        Commands\MarkWeekendAsWeekoff::class,
        Commands\MarkHolidayAttendance::class,
        Commands\MarkExpiredAnnouncements::class,
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
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'ensure.company' => \App\Http\Middleware\EnsureCompanyAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->withSchedule(function (Schedule $schedule) {
        // Master attendance scheduler - runs all attendance commands in order
        // $schedule->command('attendance:run-all')
        //     ->dailyAt('19:00')  
        //     // ->everyMinute()
        //     ->timezone('Asia/Kolkata')
        //     ->withoutOverlapping()
        //     ->appendOutputTo(storage_path('logs/attendance.log'))
        //     ->description('Run all attendance marking commands in order');
        
        
        
        // Testing Purpose only
    //     $schedule->call(function () {
    //         \Log::info('✅ Closure-based cron test ran at: ' . now()->toDateTimeString());
    //     // Artisan::call('attendance:run-all');
    //  })
    //     ->everyMinute()
    //     ->timezone('Asia/Kolkata')
    //     // ->withoutOverlapping()
    //     // // ->appendOutputTo(storage_path('logs/attendance.log'))
    //     ->description('Run all attendance marking commands in order');

        $schedule->call(function () {
            // Log internally (Laravel log)
            \Log::info('✅ Attendance cron triggered at: ' . now()->toDateTimeString());

            // Run the command manually
            \Artisan::call('attendance:run-all');

            // Optionally: log output from the command (useful for debugging)
            \Log::info(Artisan::output());

        })->name('attendance-run-all')
        ->dailyAt('19:00') 
        //   ->everyMinute()
        ->timezone('Asia/Kolkata')
        ->withoutOverlapping()
        ->description('Run all attendance marking commands in order');

        $schedule->call(function () {
            // Log internally (Laravel log)
            \Log::info('✅ Expired announcements cron triggered at: ' . now()->toDateTimeString());

            // Run the command manually
            \Artisan::call('app:mark-expired-announcements');

            // Optionally: log output from the command (useful for debugging)
            \Log::info(Artisan::output());

        })->name('mark-expired-announcements')
        ->dailyAt('00:10')
        ->timezone('Asia/Kolkata')
        ->description('Soft delete expired announcements automatically');




            
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
