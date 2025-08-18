<?php

namespace App\Console\Commands\Automation;

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
    protected $signature = 'app:train-predictions-handler {--task=} {--ignore-timing} {--competition=} {--season=} {--sync} {--prefer-saved-matches} {--is-grid-search} {--predictor-url=} {--target=} ';
    // example: php artisan app:train-predictions-handler --task=train --ignore-timing --competition=1340 --prefer-saved-matches --is-grid-search --predictor-url=http://127.0.0.1:8000 

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
        $season_id = $this->option('season');


        $prefer_saved_matches = $this->option('prefer-saved-matches') ?? false;
        $is_grid_search = $this->option('is-grid-search') ?? true;
        $predictor_url = $this->option('predictor-url') ?? null;
        $target = $this->option('target') ?? null;

        $options = [
            'prefer_saved_matches' => $prefer_saved_matches,
            'is_grid_search' => $is_grid_search,
            'predictor_url' => $predictor_url,
            'target' => $target,
        ];

        $sync = $this->option('sync');

        $params = [
            $task,
            null,
            $ignore_timing,
            $competition_id,
            $season_id,
            $options,
        ];

        if ($sync) {
            TrainPredictionsHandlerJob::dispatchSync(...$params);
            $this->info('Job executed synchronously.');
        } else {
            TrainPredictionsHandlerJob::dispatch(...$params);
            $this->info('Job dispatched to queue.');
        }
        $this->info('Train Predictions handler command executed successfully!');

        return 1;
    }
}
