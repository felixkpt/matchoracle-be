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
    protected $signature = 'app:predictions-handler {--task=} {--last-action-delay=} {--competition=} {--season=} {--sync} {--predictor-url=} {--target=} {--date=} {--match=}';

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

        if ($task != 'current_predictions' && $task != 'historical_predictions') {
            $this->warn('Task should be current_predictions or historical_predictions');
            return 1;
        }

        $this->info('Task: ' . Str::title(preg_replace('#_#', ' ', $task)));
        $last_action_delay = $this->option('last-action-delay');
        $last_action_delay = $last_action_delay !== null ? intval($last_action_delay) * 60 : null;

        $currentHour = Carbon::now()->format('H');
        $isWeekday = Carbon::now()->isWeekday();

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

        $predictor_url = $this->option('predictor-url') ?? null;
        $target = $this->option('target') ?? null;
        $date = $this->option('date') ?? null;
        $match = $this->option('match') ?? null;

        $options = [
            'predictor_url' => $predictor_url,
            'target' => $target,
            'date' => $date,
            'match' => $match,
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
            PredictionsHandlerJob::dispatchSync(...$params);
            $this->info('Job executed synchronously.');
        } else {
            PredictionsHandlerJob::dispatch(...$params);
            $this->info('Job dispatched to queue.');
        }
        $this->info('Predictions handler command executed successfully!');

        return 1;
    }
}
