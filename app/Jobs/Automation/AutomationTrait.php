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
        // Check if the column is specified and there are seasons to update.
        if ($column && $should_update_last_action) {
            
            // Update or create the last action entry with the current timestamp.
            $model
                ->lastAction()
                ->updateOrCreate(
                    [$field => $model->id],
                    [
                        $field => $model->id,
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
