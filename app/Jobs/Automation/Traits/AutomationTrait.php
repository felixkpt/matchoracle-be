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

    /**
     * Logs the start and end messages for a job, including competition details.
     *
     * @param string $message The message to log.
     * @param mixed|null $competitions The competitions associated with the job (optional).
     */
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

    /**
     * Logs an informational message for the automation job.
     *
     * @param string $message The message to log.
     */
    protected function automationInfo($message): void
    {
        $message = class_basename($this) . '-' . $this->jobId . ": " . $message;

        echo $message . "\n";
        Log::channel($this->channel)->info($message);
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
     * Applies a delay condition based on the last action column and the specified delay in minutes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query to apply the delay condition to.
     * @param string $column The column representing the last action time.
     * @param int $delay_in_minutes The delay in minutes before an action can occur.
     * @param string $table The table containing the last action (default: 'competition_last_actions').
     */
    private function lastActionDelay($query, $column, $delay_in_minutes, $table = 'competition_last_actions')
    {
        // Apply a condition to the query to check for null or delayed last action.
        $query->whereNull($table . '.' . $column)
            ->orWhere($table . '.' . $column, '<=', Carbon::now()->subMinutes($delay_in_minutes));
    }

    /**
     * Updates or creates the last action entry for the given model and column.
     *
     * @param mixed $model The model to update the last action for.
     * @param mixed $should_update_last_action Whether to update the last action.
     * @param string $column The column to update.
     * @param string $field The field representing the model ID (default: 'competition_id').
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

    /**
     * Increments the completed competition count for the logger model.
     *
     * @param mixed|null $loggerModel The logger model to increment the count for (optional).
     */
    private function incrementCompletedCompetitionCounts($loggerModel = null)
    {
        $exists = $loggerModel ? $this->$loggerModel() : $this->loggerModel();

        if ($exists) {
            $exists->update(['run_competition_counts' => $exists->run_competition_counts + 1]);
        }
    }

    /**
     * Checks if the maximum script execution time has been exceeded.
     *
     * @return bool True if the maximum execution time is exceeded, false otherwise.
     */
    private function runTimeExceeded()
    {
        // Check elapsed time before the next iteration
        if (time() - $this->startTime >= $this->maxExecutionTime) {

            // Getting the class name dynamically
            $jobName = class_basename($this) . '-' . $this->jobId;

            $msg = "Script execution time exceeded. Terminating...";

            Log::channel($this->channel)->critical('Run Time Exceeded for ' . $jobName . ': ' . $msg);
            echo $msg . "\n";

            return true;
        }
        return false;
    }
}
