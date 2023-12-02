<?php

namespace App\Console\Commands\Statistics;

use App\Jobs\CompetitionStatisticsJob;
use Illuminate\Console\Command;

class CompetitionStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:competition-statistics';

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
        $this->info('CompetitionStatisticsJob Started Successfully.');
        dispatch(new CompetitionStatisticsJob());
    }
}
