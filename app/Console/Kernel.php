<?php

namespace App\Console;

use App\Console\Commands\sendAssurancesMailsCommand;
use App\Console\Commands\sendVisitesMailsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(sendVisitesMailsCommand::class)->dailyAt('13:00');
        $schedule->command(sendAssurancesMailsCommand::class)->dailyAt('13:00');
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
