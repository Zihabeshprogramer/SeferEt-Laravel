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
        // $schedule->command('inspire')->hourly();
        
        // Clean up expired package drafts daily at 2 AM
        $schedule->command('drafts:cleanup-packages --force')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Check for expired service requests every 15 minutes
        $schedule->command('service-requests:expire')
                 ->everyFifteenMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();
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
