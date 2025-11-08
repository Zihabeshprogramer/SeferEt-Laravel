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
        
        // Unfeature expired products daily at 3 AM
        $schedule->command('featured:cleanup')
                 ->dailyAt('03:00')
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Process ad scheduling (activate/expire ads, check limits) every minute
        $schedule->job(new \App\Jobs\ProcessAdScheduling)
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();
        
        // Aggregate ad analytics daily at 1 AM (for previous day)
        $schedule->job(new \App\Jobs\AggregateAdAnalytics)
                 ->dailyAt('01:00')
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
