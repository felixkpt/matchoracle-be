<?php

namespace App\Jobs\Automation\Traits;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait AutomationTrait
{
    protected $sourceContext;
    protected $maxExecutionTime;
    protected $startTime;
    protected $channel = 'automation';
    protected $historyStartDate = '2015-01-01';

    protected function jobStartEndLog($message, $competitions = null): void
    {

        // Getting the class name dynamically
        $jobName = class_basename($this) . '-' . $this->jobId;

        $competitionIds = $competitions ? $competitions->pluck('id')->implode(', ') : 'None';

        $formattedMessage = sprintf(
            "%s: %s, Task: %s%s",
            $jobName,
            $message,
            $this->task,
            ', Competition #' . ($this->competition_id ?? 'N/A'),
        );

        $competitionsMsg = $competitions ? $jobName . ': Working on competitions IDs: [' . $competitionIds . ']' : '';

        echo $formattedMessage . "\n";
        if ($competitionsMsg) {
            echo $competitionsMsg . "\n";
        }

        if ($message == 'END') {
            $formattedMessage .= "\n";
        }

        Log::channel($this->channel)->info($formattedMessage);
        if ($competitionsMsg) {
            Log::channel($this->channel)->info($competitionsMsg);
        }
    }

    protected function automationInfo($message): void
    {
        $message = class_basename($this) . '-' . $this->jobId . ": " . $message;

        echo $message . "\n";
        Log::channel($this->channel)->info($message);
    }

    /**
     * Increment the competition run count in the logger model.
     */
    private function doCompetitionRunLogging($logger = null)
    {
        // Retrieve the logger model and update the competition run count.
        if (!$logger) {
            $record = $this->loggerModel();
        } else {
            $record = $this->$logger();
        }

        if ($record) {
            // $record->update(['run_competition_counts' => $record->run_competition_counts + 1]);
        }
    }

    /**
     * Log a failure message with the provided model and data.
     *
     * @param mixed $model
     * @param array $data
     */
    private function logFailure($model, $data)
    {
        try {
            // Create a failure log entry with the current date and provided message.
            $model->create(['date' => Carbon::now(), 'message' => $data['message']]);
        } catch (\Exception $e) {
            Log::channel($this->channel)->error("Failed to log failure: " . $e->getMessage());
        }
    }

    /**
     * Apply a delay condition based on the last action column and specified delay in minutes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param int $delay_in_minutes
     */
    private function lastActionDelay($query, $column, $delay_in_minutes, $table = 'competition_last_actions')
    {
        // Apply a condition to the query to check for null or delayed last action.
        $query->whereNull($table . '.' . $column)
            ->orWhere($table . '.' . $column, '<=', Carbon::now()->subMinutes($delay_in_minutes));
    }

    /**
     * Update or create the last action entry for the model and specified column.
     *
     * @param $model
     * @param mixed $should_update_last_action
     * @param string $column
     */
    private function updateLastAction($model, $should_update_last_action, $column, $field = 'competition_id')
    {
        if ($column && $should_update_last_action) {
            try {
                DB::transaction(function () use ($model, $column, $field) {
                    $lastAction = $model->lastAction()->where($field, $model->id)->first();

                    if ($lastAction) {
                        $lastAction->update([$column => now()]);
                    } else {
                        $model->lastAction()->create([
                            $field => $model->id,
                            $column => now(),
                        ]);
                    }
                });
            } catch (\Exception $e) {
                Log::channel($this->channel)->error("Failed to update last action: " . $e->getMessage());
            }
        }
    }

    private function runTimeExceeded()
    {
        // Check elapsed time before the next iteration
        if (time() - $this->startTime >= $this->maxExecutionTime) {

            // Getting the class name dynamically
            $jobName = class_basename($this) . '-' . $this->jobId;

            $msg = "Script execution time exceeded. Terminating...";

            Log::channel($this->channel)->critical('Run Time Exceeded for ' . $jobName . ': ' . $msg);
            echo $msg . "";

            return true;
        }
        return false;
    }

    private function incrementCompletedCompetitionCounts($loggerModel = null)
    {
        $exists = $loggerModel ? $this->$loggerModel() : $this->loggerModel();

        if ($exists) {
            $exists->update(['run_competition_counts' => $exists->run_competition_counts + 1]);
        }
    }
}
