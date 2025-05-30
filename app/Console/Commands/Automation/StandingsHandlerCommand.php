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
    protected $signature = 'app:standings-handler {--task=} {--ignore-timing} {--competition=}';
    // php artisan app:standings-handler --task=historical_results --ignore-timing --competition=1340
    
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
        $task = $this->option('task') ?? 'recent_results';
        $ignore_timing = $this->option('ignore-timing');

        if ($task != 'recent_results' && $task != 'historical_results') {
            $this->warn('Task should be either recent_results or historical_results');
            return 0;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));

        $competition_id = $this->option('competition');

        dispatch(new StandingsHandlerJob($task, null, $ignore_timing, $competition_id));
        $this->info('Standings handler command executed successfully!');
    }
}
