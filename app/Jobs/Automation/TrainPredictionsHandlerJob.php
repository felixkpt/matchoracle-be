<?php

namespace App\Jobs\Automation;

use App\Models\Competition;
use App\Models\FailedTrainPredictionLog;
use App\Models\TrainPredictionJobLog;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TrainPredictionsHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * The task to be performed by the job.
     *
     * @var string
     */
    protected $task = 'train';
    protected $ignore_date;

    /**
     * Create a new job instance.
     */
    public function __construct($task)
    {
        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 10;
        $this->startTime = time();

        // Instantiate the context class for handling game sources
        $this->sourceContext = new GameSourceStrategy();

        // Set the initial game source strategy (can be switched dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
        // Set the task property
        if ($task) {
            $this->task = $task;
        }
    }

    /**
     * Execute the job to fetch matches for competitions.
     */
    public function handle(): void
    {
        $this->loggerModel(true);

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $lastFetchColumn = 'predictions_last_train';

        // Set delay in minutes based on the task type:
        // Default case for train
        $delay = 60 * 24 * 2;

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn ($q) => $q->where('status_id', activeStatusId()))
            ->where(fn ($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(1000)->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
            ->get();

        // Loop through each competition to fetch and update matches
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        foreach ($competitions as $key => $competition) {

            $last_action = $competition->{$lastFetchColumn} ?? 'N/A';
            echo ($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name}), last trained: {$last_action}\n";

            $this->doCompetitionRunLogging();

            $command = '/usr/bin/python3 ~/Documents/Dev/python/matchoracle-predictions-v2/main.py train --competition=' . $competition->id;

            exec($command, $output, $returnCode);

            echo "Return Code: $returnCode\n";

            echo "Output:\n";
            foreach ($output as $line) {
                echo $line . "\n";
            }

            if ($returnCode === 0) {
                echo "Python script ran successfully!\n";
                $should_sleep_for_competitions = true;
            } else {
                echo "Error: Python script failed to run. Check the output for details.\n";
                $should_sleep_for_competitions = false;
            }

            $data['message'] = '';
            echo $data['message'] . "\n";

            $should_update_last_action = true;
            $this->doLogging($data);
            $this->updateLastAction($competition, $should_update_last_action, $lastFetchColumn);

            echo "------------\n";

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? 10 : 0);
            $should_sleep_for_competitions = false;
        }
    }

    private function lastActionFilters($query)
    {
        // Conditionally filter games based on the task being performed
        $query = $query->when($this->task == 'task', function ($q) {
            $q->where('utc_date', '<', Carbon::now()->subDays(5));
        });

        return $query;
    }

    private function doLogging($data = null)
    {
        $updated_matches_counts = $data['results']['saved_updated'] ?? 0;
        $train_success_counts = $updated_matches_counts > 0 ? 1 : 0;
        $fetch_failed_counts = $data ? ($updated_matches_counts === 0 ? 1 : 0) : 0;

        $exists = $this->loggerModel();

        if ($exists) {
            $arr = [
                'job_run_counts' => $exists->job_run_counts + 1,
                'train_success_counts' => $exists->train_success_counts + $train_success_counts,
                'train_failed_counts' => $exists->train_failed_counts + $updated_matches_counts,
                'updated_matches_counts' => $exists->updated_matches_counts + $updated_matches_counts,
            ];

            $exists->update($arr);

            if ($fetch_failed_counts) $this->logFailure(new FailedTrainPredictionLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false)
    {
        $today = Carbon::now()->format('Y-m-d');
        $record = TrainPredictionJobLog::where('prediction_type_id', default_prediction_type())->where('date', $today)->first();

        if (!$record) {
            $arr = [
                'prediction_type_id' => default_prediction_type(),
                'date' => $today,
                'job_run_counts' => 1,
                'competition_run_counts' => 0,
                'train_run_counts' => 0,
                'train_success_counts' => 0,
                'train_failed_counts' => 0,
                'trained_counts' => 0,
            ];

            $record = TrainPredictionJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
