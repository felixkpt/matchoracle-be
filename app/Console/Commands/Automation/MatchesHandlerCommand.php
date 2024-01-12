<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\MatchesHandlerJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MatchesHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:matches-handler {--task=} {--ignore-date}';

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
        $task = $this->option('task') ?? 'recent_results';

        if ($task != 'recent_results' && $task != 'historical_results' && $task != 'shallow_fixtures' && $task != 'fixtures') {
            $this->warn('Task should be recent_results, historical_results, shallow_fixtures or fixtures');
            return 0;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));

        $ignore_date = $this->option('ignore-date');

        dispatch(new MatchesHandlerJob($task, $ignore_date));
        $this->info('Matches handler command executed successfully!');
    }
}
