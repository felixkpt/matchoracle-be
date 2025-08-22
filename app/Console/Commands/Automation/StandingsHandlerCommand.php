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
    protected $signature = 'app:standings-handler {--task=} {--ignore-timing} {--competition=} {--season=} {--sync}';
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
        $task = $this->option('task') ?? 'historical_results';
        $ignore_timing = $this->option('ignore-timing');

        if ($task != 'recent_results' && $task != 'historical_results') {
            $this->warn('Task should be either recent_results or historical_results');
            return 0;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));

        $competition_id = $this->option('competition');
        $season_id = $this->option('season');
        $sync = $this->option('sync');

        $params = [
            $task,
            null,
            $ignore_timing,
            $competition_id,
            $season_id,
        ];

        if ($sync) {
            StandingsHandlerJob::dispatchSync(...$params);
            $this->info('Job executed synchronously.');
        } else {
            StandingsHandlerJob::dispatch(...$params);
            $this->info('Job dispatched to queue.');
        }
        $this->info('Standings handler command executed successfully!');

        return 1;
    }
}
