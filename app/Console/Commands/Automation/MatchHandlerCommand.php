<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\MatchHandlerJob;
use Illuminate\Console\Command;

class MatchHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:match-handler {--task=} {--ignore-date}';

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
        
        if ($task != 'historical_results' && $task != 'results' && $task != 'fixtures') {
            $this->warn('Task should be historical_results, results or fixtures.');
            return 0;
        }

        $this->info('Task: ' . ($task == 'results' ? 'results update.' : 'fixtures update.'));

        dispatch(new MatchHandlerJob($task, $ignore_date));
        $this->info('Match handler command executed successfully!');
    }
}
