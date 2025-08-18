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
    protected $signature = 'app:seasons-handler {--ignore-timing} {--ignore-status} {--competition=} {--sync}';

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

        $competition_id = $this->option('competition');
        $sync = $this->option('sync');

        $params = [
            null,
            null,
            $ignore_timing,
            $competition_id,
        ];

        if ($sync) {
            SeasonsHandlerJob::dispatchSync(...$params);
            $this->info('Job executed synchronously.');
        } else {
            SeasonsHandlerJob::dispatch(...$params);
            $this->info('Job dispatched to queue.');
        }
        $this->info('Seasons handler command executed successfully!');

        return 1;
    }
}
