<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\StandingsHandlerJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class StandingsHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:standings-handler {--task=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $task = $this->option('task') ?? 'results';

        if ($task != 'recent_results' && $task != 'historical_results') {
            $this->warn('Task should be either recent_results or historical_results');
            return 0;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));

        dispatch(new StandingsHandlerJob($task));
        $this->info('Standings handler command executed successfully!');
    }
}
