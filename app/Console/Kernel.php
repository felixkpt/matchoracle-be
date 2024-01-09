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
        // $schedule->command('inspire')->hourly();
        $schedule->command('seeders:run')
            ->everyThirtyMinutes()
            ->withoutOverlapping();
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
