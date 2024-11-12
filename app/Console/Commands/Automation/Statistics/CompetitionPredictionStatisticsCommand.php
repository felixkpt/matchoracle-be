<?php

namespace App\Console\Commands\Automation\Statistics;

use App\Jobs\Automation\Statistics\CompetitionPredictionStatisticsJob;
use Illuminate\Console\Command;

class CompetitionPredictionStatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:competition-prediction-statistics {--competition=} {--ignore-timing}';

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
        $competitionId = $this->option('competition');
        $ignore_timing = $this->option('ignore-timing');

        dispatch(new CompetitionPredictionStatisticsJob(null, null, $ignore_timing, $competitionId));
        $this->info('Competition Prediction Statistics Job command executed successfully!');
    }
}
