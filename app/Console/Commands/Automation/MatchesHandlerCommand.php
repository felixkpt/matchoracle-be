<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\MatchesHandlerJob;
use Illuminate\Console\Command;

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

        if ($task != 'results' && $task != 'fixtures') {
            $this->warn('Task should be either results or fixtures');
            return 0;
        }

        $this->info('Task: ' . ($task == 'results' ? 'results update.' : 'fixtures update.'));

        dispatch(new MatchesHandlerJob($task, $ignore_date));
        $this->info('Matches handler command executed successfully!');
    }
}
