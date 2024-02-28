<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\PredictionsHandlerJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PredictionsHandlerCommand extends Command
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
        $task = $this->option('task') ?? 'recent_results';
        $ignore_date = $this->option('ignore-date');

        if ($task != 'recent_results' && $task != 'historical_results' && $task != 'shallow_fixtures' && $task != 'fixtures') {
            $this->warn('Task should be recent_results, historical_results, shallow_fixtures or fixtures');
            return 0;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));

        dispatch(new PredictionsHandlerJob($task, $ignore_date));
        $this->info('Predictions handler command executed successfully!');
    }
}
