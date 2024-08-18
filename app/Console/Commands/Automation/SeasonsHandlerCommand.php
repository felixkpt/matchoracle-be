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
    protected $signature = 'app:seasons-handler {--ignore-timing} {--ignore-status}';

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
        $ignore_timing = $this->option('ignore-timing');

        dispatch(new SeasonsHandlerJob($ignore_timing));
        $this->info('Seasons handler command executed successfully!');
    }
}
