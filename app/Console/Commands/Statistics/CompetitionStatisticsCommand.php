<?php

namespace App\Console\Commands\Statistics;

use App\Jobs\Statistics\CompetitionStatisticsJob;
use Illuminate\Console\Command;

class CompetitionStatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:competition-statistics {--competition=}';

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
        dispatch(new CompetitionStatisticsJob($competitionId));
        $this->info('Competition Statistics Job command executed successfully!');
    }
}
