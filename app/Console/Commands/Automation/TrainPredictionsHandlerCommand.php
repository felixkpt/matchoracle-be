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
    protected $signature = 'app:train-predictions-handler {--task=} {--last-action-delay=} {--competition=} {--season=} {--sync} {--prefer-saved-matches} {--is-random-search} {--predictor-url=} {--target=} {--model-type=}';
    // example: php artisan app:train-predictions-handler --task=train --last-action-delay --competition=1340 --prefer-saved-matches --is-grid-search --predictor-url=http://127.0.0.1:8000 

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

        if ($task != 'current_predictions' && $task != 'historical_predictions') {
            $this->warn('Task should be current_predictions or historical_predictions');
            return 1;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));
        $last_action_delay = $this->option('last-action-delay');
        $last_action_delay = $last_action_delay !== null ? intval($last_action_delay) * 60 : null;

        $blockedRange = config('automation.blocked_hours');
        [$startHour, $endHour] = array_map('intval', explode('-', $blockedRange));

        $currentHour = now()->hour;
        $isWeekday   = now()->isWeekday();

        if ($this->option('last-action-delay') !== null && $isWeekday && $currentHour >= $startHour && $currentHour < $endHour) {
            $this->warn("This command can only be executed outside of {$startHour}:00 to {$endHour}:00 on weekdays, but runs anytime on weekends.");
            return 1;
        }

        $competition_id = $this->option('competition');
        $season_id = $this->option('season');

        $prefer_saved_matches = $this->option('prefer-saved-matches') ?: false;
        $is_random_search = $this->option('is-random-search') ?: false;
        $predictor_url = $this->option('predictor-url') ?? null;
        $target = $this->option('target') ?? null;
        $modelType = $this->option('model-type') ?? null;

        $options = [
            'prefer_saved_matches' => $prefer_saved_matches,
            'is_grid_search' => true,
            'is_random_search' => $is_random_search,
            'predictor_url' => $predictor_url,
            'target' => $target,
            'modelType' => $modelType,
        ];

        $sync = $this->option('sync');

        $params = [
            $task,
            null,
            $last_action_delay,
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
