<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check for approaching deadlines every day at 8:00 AM
        $schedule->command('akreditasi:check-deadlines')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->runInBackground()
            ->emailOutputOnFailure(config('mail.admin_email'));

        // Optional: Run more frequently during critical periods
        // Uncomment to check deadlines every 6 hours
        // $schedule->command('akreditasi:check-deadlines')
        //     ->everySixHours()
        //     ->withoutOverlapping()
        //     ->runInBackground();

        // Clean up old read notifications every week
        $schedule->call(function () {
            app(\App\Services\NotificationService::class)->deleteOldNotifications(30);
        })->weekly()->sundays()->at('02:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
