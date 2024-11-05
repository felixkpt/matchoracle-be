<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\PredictionsHandlerJob;
use App\Jobs\Automation\TrainPredictionsHandlerJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TrainPredictionsHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:train-predictions-handler {--task=} {--ignore-timing} {--competition=}';

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
        $currentHour = Carbon::now()->format('H');

        // Restrict execution between 8 AM (08) and 4 PM (16)
        if ($currentHour < 8 || $currentHour >= 16) {
            $this->warn('This command can only be executed between 8 AM and 4 PM.');
            return 1; // Return a non-zero status code for failure
        }

        $task = $this->option('task') ?? 'train';

        if ($task != 'train') {
            $this->warn('Task should be train');
            return 1;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));
        $ignore_timing = $this->option('ignore-timing');

        $competition_id = $this->option('competition');
        dispatch(new TrainPredictionsHandlerJob($task, null, $ignore_timing, $competition_id));
        $this->info('Train Predictions handler command executed successfully!');

        return 0;
    }
}
