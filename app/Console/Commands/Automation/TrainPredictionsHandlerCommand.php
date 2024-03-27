<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\PredictionsHandlerJob;
use App\Jobs\Automation\TrainPredictionsHandlerJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TrainPredictionsHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:train-predictions-handler {--task=}';

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
        $task = $this->option('task') ?? 'train';
        
        if ($task != 'train') {
            $this->warn('Task should be train');
            return 0;               
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));

        dispatch(new TrainPredictionsHandlerJob($task));
        $this->info('Train Predictions handler command executed successfully!');
    }
}
