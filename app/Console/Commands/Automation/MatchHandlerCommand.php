<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\MatchHandlerJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MatchHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:match-handler {--task=} {--ignore-timing} {--match-id=}';

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
        $ignore_timing = $this->option('ignore-timing');
        $match_id = $this->option('match-id');
      
        if ($task != 'recent_results' && $task != 'historical_results' && $task != 'shallow_fixtures' && $task != 'fixtures') {
            $this->warn('Task should be recent_results, historical_results, shallow_fixtures or fixtures');
            return 0;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));

        dispatch(new MatchHandlerJob($task, $ignore_timing, $match_id));
        $this->info('Match handler command executed successfully!');

        return 1;
    }
}
