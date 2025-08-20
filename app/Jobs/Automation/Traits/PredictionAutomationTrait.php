<?php

namespace App\Jobs\Automation\Traits;

use App\Models\Game;
use App\Models\PredictionJobLog;
use App\Models\TrainPredictionJobLog;
use App\Repositories\Game\GameRepository;
use Illuminate\Support\Carbon;

trait PredictionAutomationTrait
{

    /**
     * Retrieves or creates a prediction log record for a given date and model.
     *
     * @param string $logModel The model class to query (e.g., PredictionJobLog or TrainPredictionJobLog)
     * @param string $date The date for which to retrieve or create the prediction log
     * @return array Contains the prediction type and existing record for the given date
     */
    protected function getPredictionLogRecord($logModel, $date)
    {
        $prediction_type = (new GameRepository(new Game()))->updateOrCreatePredictorOptions();

        $record = $logModel::where('date', $date)
            ->where('prediction_type_id', $prediction_type->id)
            ->first();

        return [
            'prediction_type' => $prediction_type,
            'record' => $record
        ];
    }

    /**
     * Logs or updates the training prediction job record for the current date.
     *
     * @param bool $increment_job_run_counts Whether to increment the job run count if a record exists
     * @param int|null $competition_counts The count of competitions (required for new records)
     * @param int|null $action_counts The count of actions (optional for new records)
     * @return TrainPredictionJobLog The training prediction log record for the current date
     */
    protected function trainPredictionsLoggerModel($increment_job_run_counts = false, $competition_counts = null, $action_counts = null)
    {
        if ($this->competitionId) {
            return;
        }

        $today = Carbon::now()->format('Y-m-d');

        // Retrieve prediction log record for today's date
        $result = $this->getPredictionLogRecord(TrainPredictionJobLog::class, $today);
        $prediction_type = $result['prediction_type'];
        $record = $result['record'];

        // Create new log entry if record doesn't exist
        if (!$record) {

            $arr = [
                'date' => $today,
                'prediction_type_id' => $prediction_type->id,
                'job_run_counts' => 1,
                'competition_counts' => $competition_counts,
                'action_counts' => $action_counts,
            ];

            $record = TrainPredictionJobLog::create($arr);
        } elseif ($increment_job_run_counts) {
            // Increment job run count if specified
            $record->update(['job_run_counts' => $record->job_run_counts + 1]);
        }

        return $record;
    }

    /**
     * Logs or updates the prediction job record for the current date.
     *
     * @param bool $increment_job_run_counts Whether to increment the job run count if a record exists
     * @param int|null $competition_counts The count of competitions (required for new records)
     * @param int|null $action_counts The count of actions (optional for new records)
     * @return PredictionJobLog The prediction job log record for the current date
     */
    protected function predictionsLoggerModel($increment_job_run_counts = false, $competition_counts = null, $action_counts = null)
    {
        $today = Carbon::now()->format('Y-m-d');

        // Retrieve prediction log record for today's date
        $result = $this->getPredictionLogRecord(PredictionJobLog::class, $today);
        $prediction_type = $result['prediction_type'];
        $record = $result['record'];

        // Create new log entry if record doesn't exist
        if (!$record) {

            $arr = [
                'date' => $today,
                'prediction_type_id' => $prediction_type->id,
                'job_run_counts' => 1,
                'competition_counts' => $competition_counts,
                'action_counts' => $action_counts,
            ];

            $record = PredictionJobLog::create($arr);
        } elseif ($increment_job_run_counts) {
            // Increment job run count if specified
            $record->update(['job_run_counts' => $record->job_run_counts + 1]);
        }

        return $record;
    }

    /**
     * Logs the polling attempt message with an estimated remaining time.
     *
     * @param int $attempt Current polling attempt number
     * @param int $totalPolls Total number of polling attempts allowed
     * @param Carbon\Carbon $startTime Start time of the polling process
     * @param int $maxWaitTime Maximum wait time in seconds
     */
    private function logPollingAttempt($attempt, $totalPolls, $startTime, $maxWaitTime)
    {
        // Calculate elapsed time and remaining time
        $elapsedTime = now()->diffInSeconds($startTime);
        $remainingTime = ceil(($maxWaitTime - $elapsedTime) / 60); // in minutes

        // Log polling attempt message
        $this->automationInfo("***Polling attempt {$attempt} of {$totalPolls} (~{$remainingTime} mins left) - Checking process status...");
    }
}
