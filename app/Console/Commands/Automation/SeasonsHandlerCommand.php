<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\SeasonsHandlerJob;
use Illuminate\Console\Command;

class SeasonsHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seasons-handler {--ignore-status}';

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
        request()->merge(['ignore_status' => !!$this->option('ignore-status')]);

        dispatch(new SeasonsHandlerJob());
        $this->info('Seasons handler command executed successfully!');
    }
}
