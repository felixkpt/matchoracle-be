<?php

namespace App\Console\Commands\Statistics;

use App\Jobs\CompetitionPredictionStatisticsJob;
use Illuminate\Console\Command;

class CompetitionPredictionStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:competition-prediction-statistics';

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
        $this->info('CompetitionPredictionStatisticsJob Started Successfully.');
        dispatch(new CompetitionPredictionStatisticsJob());
    }
}
