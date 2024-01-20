<?php

namespace App\Console\Commands\Statistics;

use App\Jobs\Statistics\BettingTipsStatisticsJob;
use Illuminate\Console\Command;

class BettingTipsStatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:betting-tips-statistics {--type=}';

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
        $type = $this->option('type');

        dispatch(new BettingTipsStatisticsJob($type));
        $this->info('Betting Tips Statistics Job command executed successfully!');
    }
}
