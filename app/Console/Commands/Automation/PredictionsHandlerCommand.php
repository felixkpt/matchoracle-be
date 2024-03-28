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
    protected $signature = 'app:predictions-handler {--task=}';

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
        $task = $this->option('task') ?? 'run';

        if ($task != 'run') {
            $this->warn('Task should be run');
            return 0;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));

        dispatch(new PredictionsHandlerJob($task));
        $this->info('Predictions handler command executed successfully!');
    }
}
