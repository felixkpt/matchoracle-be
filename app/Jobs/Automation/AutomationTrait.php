<?php

namespace App\Jobs\Automation;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

trait AutomationTrait
{
    protected $sourceContext;
    protected $maxExecutionTime;
    protected $startTime;

    /**
     * Increment the competition run count in the logger model.
     */
    private function doCompetitionRunLogging()
    {
        // Retrieve the logger model and update the competition run count.
        $record = $this->loggerModel();

        if ($record) {
            $record->update(['competition_run_counts' => $record->competition_run_counts + 1]);
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
        // Create a failure log entry with the current date and provided message.
        $model->create(['date' => Carbon::now(), 'message' => $data['message']]);
    }

    /**
     * Apply a delay condition based on the last action column and specified delay in minutes.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param int $delay_in_minutes
     */
    private function lastActionDelay($query, $column, $delay_in_minutes)
    {
        // Apply a condition to the query to check for null or delayed last action.
        $query->whereNull('competition_last_actions.' . $column)
            ->orWhere('competition_last_actions.' . $column, '<=', Carbon::now()->subMinutes($delay_in_minutes));
    }

    /**
     * Update or create the last action entry for the competition and specified column.
     *
     * @param \App\Models\Competition $competition
     * @param mixed $should_update_last_action
     * @param string $column
     */
    private function updateLastAction($competition, $should_update_last_action, $column)
    {
        // Check if the column is specified and there are seasons to update.
        if ($column && $should_update_last_action) {
            // Update or create the last action entry with the current timestamp.
            $competition
                ->lastAction()
                ->updateOrCreate(
                    ['competition_id' => $competition->id],
                    [
                        'competition_id' => $competition->id,
                        $column => now(),
                    ]
                );
        }
    }

    private function runTimeExceeded()
    {
        // Check elapsed time before the next iteration
        if (time() - $this->startTime >= $this->maxExecutionTime) {

            // Getting the class name dynamically
            $className = get_class($this);

            $msg = "Script execution time exceeded. Terminating...";

            Log::critical('Run Time Exceeded for ' . $className . ': ' . $msg);
            echo $msg . "\n";

            return true;
        }
        return false;
    }
}
