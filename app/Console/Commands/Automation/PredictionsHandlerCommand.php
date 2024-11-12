<?php

namespace App\Console\Commands\Automation;

use App\Jobs\Automation\PredictionsHandlerJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PredictionsHandlerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:predictions-handler {--task=} {--ignore-timing} {--competition=} {--predictor-url=} {--target=}';

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
            return 1;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));
        $ignore_timing = $this->option('ignore-timing');

        $currentHour = Carbon::now()->format('H');
        $isWeekday = Carbon::now()->isWeekday();

        if (!$ignore_timing && $isWeekday && ($currentHour >= 8 && $currentHour < 16)) {
            $this->warn('This command can only be executed outside of 8 AM to 4 PM on weekdays, but runs anytime on weekends.');
            return 1; // Return a non-zero status code for failure
        }

        $competition_id = $this->option('competition');
        
        $predictor_url = $this->option('predictor-url') ?? null;
        $target = $this->option('target') ?? null;

        $options = [
            'predictor_url' => $predictor_url,
            'target' => $target,
        ];

        dispatch(new PredictionsHandlerJob($task, null, $ignore_timing, $competition_id, $options));
        $this->info('Predictions handler command executed successfully!');

        return 0;
    }
}
