<?php

namespace App\Jobs\Automation;

use Illuminate\Support\Carbon;

trait AutomationTrait
{
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
     * Apply a delay condition based on the last action column and specified delay in hours.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $column
     * @param int $delay_in_hours
     */
    private function lastActionDelay($query, $column, $delay_in_hours)
    {
        // Apply a condition to the query to check for null or delayed last action.
        $query->whereNull('competition_last_actions.' . $column)
              ->orWhere('competition_last_actions.' . $column, '<=', Carbon::now()->subHours($delay_in_hours));
    }

    /**
     * Update or create the last action entry for the competition and specified column.
     *
     * @param \App\Models\Competition $competition
     * @param mixed $seasons
     * @param string $column
     */
    private function updateLastAction($competition, $seasons, $column)
    {
        // Check if the column is specified and there are seasons to update.
        if ($column && ($seasons == 'from_seasons' || $seasons->count() > 0)) {
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
}
