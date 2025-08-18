<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\CompetitionAbbreviationsHandlerJob;
use Illuminate\Console\Command;

class CompetitionAbbreviationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:competition-abbreviations {--ignore-timing} {--ignore-status} {--competition=} {--sync}';

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
            null,
        ];

        if ($sync) {
            CompetitionAbbreviationsHandlerJob::dispatchSync(...$params);
            $this->info('Job executed synchronously.');
        } else {
            CompetitionAbbreviationsHandlerJob::dispatch(...$params);
            $this->info('Job dispatched to queue.');
        }
        $this->info('CompetitionAbbreviations handler command executed successfully!');

        return 0;
    }
}
