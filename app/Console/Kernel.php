<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Generate daily reports at 11:59 PM
        $schedule->command('reports:generate --type=daily')
                ->dailyAt('23:59');

        // Generate weekly reports every Sunday at 11:59 PM
        $schedule->command('reports:generate --type=weekly')
                ->weekly()
                ->sundays()
                ->at('23:59');

        // Check for overdue assignments every hour
        $schedule->command('assignments:check-overdue')
                ->hourly();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
