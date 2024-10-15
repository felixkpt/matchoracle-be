<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Seasons commands
        $schedule->command('app:seasons-handler')->hourly();

        // Standing commands
        $schedule->command('app:standings-handler')->everySixHours(0);
        $schedule->command('app:standings-handler --task=historical_results')->everySixHours(5);
        $schedule->command('app:standings-handler --task=recent_results')->everySixHours(10);

        // Matches commands
        require('matches_commands.php');
        
        // Statistics commands
        $schedule->command('app:competition-statistics')->everySixHours(35);
        $schedule->command('app:competition-prediction-statistics')->everySixHours(40);

        // Predictions commands
        $schedule->command('app:predictions-handler')->everySixHours(45);
        $schedule->command('app:train-predictions-handler')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {

        Artisan::command('migrate:fresh {--seed}', function () {
            /** @var \Illuminate\Console\Command $cmd */
            $cmd = $this;

            $confirmed = $cmd->ask("Are you sure you want to resign?", "Yes");

            $cmd->comment('Nope!');
        })->purpose('Disable fresh command');

        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
