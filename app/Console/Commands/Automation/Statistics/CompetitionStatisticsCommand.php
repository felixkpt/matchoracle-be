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
    protected $signature = 'app:competition-statistics {--competition=} {--ignore-timing}';

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
        $this->info('Competition Statistics Job Started Successfully.');

        $competitionId = $this->option('competition');
        $ignore_timing = $this->option('ignore-timing');

        dispatch(new CompetitionStatsHandlerJob(null, null, $ignore_timing, $competitionId));
        $this->info('Competition Statistics Job command executed successfully!');
    }
}
