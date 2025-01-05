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
        $schedule->command('app:seasons-handler')->everyFourHours();

        // Standing commands
        $schedule->command('app:standings-handler --task=historical_results')->everyFourHours(5);
        $schedule->command('app:standings-handler --task=recent_results')->everyFourHours(10);

        // Statistics commands
        $schedule->command('app:competition-statistics')->everySixHours(35);
        $schedule->command('app:competition-prediction-statistics')->everySixHours(40);

        // Predictions commands
        $schedule->command('app:predictions-handler')->everyTwoHours(5);
        $schedule->command('app:train-predictions-handler')->everyTwoHours();

        // Matches commands
        require_once 'matches_commands.php';

        // Odds commands
        require_once 'odds_commands.php';
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {

        // Artisan::command('migrate:fresh {--seed}', function () {
        //     /** @var \Illuminate\Console\Command $cmd */
        //     $cmd = $this;

        //     $confirmed = $cmd->ask("Are you sure you want to resign?", "Yes");

        //     $cmd->comment('Nope!');
        // })->purpose('Disable fresh command');

        $this->load(__DIR__ . '/Commands');

        require_once base_path('routes/console.php');
    }
}
