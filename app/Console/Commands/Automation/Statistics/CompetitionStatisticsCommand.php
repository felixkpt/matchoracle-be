<?php

namespace App\Console\Commands\Automation\Statistics;

use App\Jobs\Automation\Statistics\CompetitionStatsHandlerJob;
use Illuminate\Console\Command;

class CompetitionStatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:competition-statistics {--task=} {--last-action-delay=} {--competition=} {--season=} {--sync}';

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
            CompetitionStatsHandlerJob::dispatchSync(...$params);
            $this->info('Job executed synchronously.');
        } else {
            CompetitionStatsHandlerJob::dispatch(...$params);
            $this->info('Job dispatched to queue.');
        }
        $this->info('Competition Statistics Job command executed successfully!');
    }
}
