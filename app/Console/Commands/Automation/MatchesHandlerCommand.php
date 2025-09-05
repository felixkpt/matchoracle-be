<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\MatchesHandlerJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MatchesHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:matches-handler {--task=} {--last-action-delay=} {--competition=} {--season=} {--sync}';

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
        $task = $this->option('task');

        if ($task != 'recent_results' && $task != 'historical_results' && $task != 'shallow_fixtures' && $task != 'fixtures') {
            $this->warn('Task should be recent_results, historical_results, shallow_fixtures or fixtures');
            return 0;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));
        $last_action_delay = $this->option('last-action-delay');
        $last_action_delay = $last_action_delay !== null ? intval($last_action_delay) * 60 : null;

        $competition_id = $this->option('competition');
        $season_id = $this->option('season');
        $sync = $this->option('sync');

        $params = [
            $task,
            null,
            $last_action_delay,
            $competition_id,
            $season_id,
        ];

        if ($sync) {
            MatchesHandlerJob::dispatchSync(...$params);
            $this->info('Job executed synchronously.');
        } else {
            MatchesHandlerJob::dispatch(...$params);
            $this->info('Job dispatched to queue.');
        }
        $this->info('Matches handler command executed successfully!');

        return 0;
    }
}
