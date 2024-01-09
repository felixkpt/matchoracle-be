<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\StandingsHandlerJob;
use Illuminate\Console\Command;

class StandingsHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:standings-handler';

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
        dispatch(new StandingsHandlerJob());
        $this->info('Standings handler command executed successfully!');
    }
}
