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
        $task = $this->option('task') ?? 'results';
        $ignore_date = $this->option('ignore-date');

        if ($task != 'historical_results' && $task != 'results' && $task != 'shallow_fixtures' && $task != 'fixtures') {
            $this->warn('Task should be either results or shallow_fixtures, fixtures');
            return 0;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));

        dispatch(new MatchesHandlerJob($task, $ignore_date));
        $this->info('Matches handler command executed successfully!');
    }
}
