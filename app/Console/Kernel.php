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
        $schedule->command('app:seasons-handler')->everyOddHour();

        // Standing commands
        $schedule->command('app:standings-handler --task=historical_results')->everyThreeHours();
        $schedule->command('app:standings-handler --task=recent_results')->everyThreeHours();

        // Matches commands
        $schedule->command('app:matches-handler --task=historical_results')->everyFifteenMinutes();
        $schedule->command('app:matches-handler --task=recent_results')->everyFifteenMinutes();
        $schedule->command('app:matches-handler --task=shallow_fixtures')->everyFifteenMinutes();
        $schedule->command('app:matches-handler --task=fixtures')->everyFifteenMinutes();

        // Match commands
        $schedule->command('app:match-handler --task=historical_results')->everyFifteenMinutes();
        $schedule->command('app:match-handler --task=recent_results')->everyThirtyMinutes();
        $schedule->command('app:match-handler --task=shallow_fixtures')->everyThirtyMinutes();
        $schedule->command('app:match-handler --task=fixtures')->everyThirtyMinutes();

        // Statistics commands
        $schedule->command('app:competition-statistics')->everySixHours();
        $schedule->command('app:competition-prediction-statistics')->everySixHours();
        $schedule->command('app:betting-tips-statistics')->everySixHours();

        // Predictions commands
        $schedule->command('app:predictions-handler')->everyThreeHours();
        // $schedule->command('app:train-predictions-handler')->everyThreeHours();
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

        require base_path('routes/console.php');
    }
}
