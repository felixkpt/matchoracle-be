<?php

namespace App\Jobs\Automation\Traits;

use App\Events\CompetitionActionUpdated;
use App\Models\AppSetting;
use App\Models\Competition;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Shared automation job properties and helper methods.
 *
 * Properties documented here are available in all jobs using this trait.
 *
 * @property string      $jobId            The unique identifier for the job.
 * @property string      $task             The type of task to be performed by the job.
 * @property bool        $ignoreTiming     Whether to ignore timing constraints for the job.
 * @property int|null    $competitionId    The identifier for the competition associated with the job.
 * @property int|null    $seasonId         The identifier for the season associated with the job.
 * @property int|null    $gameId           The identifier for the match associated with the job.
 *
 * @property mixed       $sourceContext    Context class for handling game sources.
 * @property int         $maxExecutionTime Maximum allowed execution time (in seconds).
 * @property int         $startTime        The start timestamp of the job.
 * @property string      $channel          The logging channel used for automation jobs (default: 'automation').
 *
 * @property string      $historyStartDate Start date for fetching historical data.
 * @property string      $predictorUrl     URL of the predictor service.
 * @property int         $delayCompetitions Delay time (in minutes) before fetching competitions.
 * @property int         $delaySeasons     Delay time (in minutes) before fetching seasons.
 * @property int         $delayGames       Delay time (in minutes) before fetching games.
 */
trait AutomationTrait
{
    // Core job properties (common across jobs)
    protected $jobId;
    protected $task;
    protected $ignoreTiming;
    protected $competitionId;
    protected $seasonId;
    protected $gameId;

    // Automation settings/properties
    protected $sourceContext;
    protected $maxExecutionTime;
    protected $startTime;
    protected $channel = 'automation';
    protected $historyStartDate;
    protected $delayCompetitions;
    protected $delaySeasons;
    protected $delayGames;
    protected $predictorUrl;

    /**
     * Load settings from the database and initialize the properties.
     */
    protected function initializeSettings(): void
    {
        $settings = AppSetting::whereIn('name', [
            'history_start_date',
            'predictor_url',
            'delay_competitions',
            'delay_seasons',
            'delay_games',
        ])->pluck('value', 'name');

        $this->historyStartDate = $settings['history_start_date'] ?? '2018-01-01';
        $this->predictorUrl = $settings['predictor_url'] ?? 'http://127.0.0.1:8085';
        $this->delayCompetitions = $settings['delay_competitions'] ?? 15;
        $this->delaySeasons = $settings['delay_seasons'] ?? 15;
        $this->delayGames = $settings['delay_games'] ?? 15;
    }

    /**
     * Logs a job lifecycle event (start or end) with competition details
     * and broadcasts the event for frontend updates.
     *
     * This method:
     *   - Logs formatted messages to both stdout and the configured logging channel.
     *   - Includes competition IDs if provided.
     *   - Triggers a `CompetitionActionUpdated` event for 'START' and 'END' messages.
     *
     * @param string $lifecycleEvent The job lifecycle event: 'START' or 'END'.
     * @param \Illuminate\Support\Collection|array|null $competitions Optional collection of competitions related to the job.
     */
    protected function logAndBroadcastJobLifecycle(string $lifecycleEvent, $competitions = null): void
    {
        $jobName = class_basename($this) . '-' . $this->jobId;

        $competitionIds = $competitions ? $competitions->pluck('id')->implode(', ') : 'None';

        $formattedMessage = sprintf(
            "%s: %s, Task: %s%s",
            $jobName,
            $lifecycleEvent,
            $this->task,
            ', Competition #' . ($this->competitionId ?? 'N/A'),
        );

        $competitionsMsg = $competitions ? $jobName . ': Working on competitions IDs: [' . $competitionIds . ']' : '';

        echo $formattedMessage . "\n";
        if ($competitionsMsg) {
            echo $competitionsMsg . "\n";
        }

        Log::channel($this->channel)->info($formattedMessage);
        if ($competitionsMsg) {
            Log::channel($this->channel)->info($competitionsMsg);
        }

        // Include queue connection in response
        $queueConnection = config('queue.default');

        if ($queueConnection !== 'sync' && $lifecycleEvent === 'START' || $lifecycleEvent === 'END') {
            $payload = $this->getJobBroadcastPayload($lifecycleEvent === 'START' ? 'started' : 'completed');
            event(new CompetitionActionUpdated($payload));
        }
    }

    protected function getCompetition()
    {
        return Competition::find($this->competitionId);
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

    /**
     * Updates or creates the last action entry for the given model and column.
     *
     * @param mixed $model The model to update the last action for.
     * @param mixed $should_update_last_action Whether to update the last action.
     * @param string $column The column to update.
     * @param string $seasonId The field representing the season.
     */
    private function updateCompetitionLastAction($model, $should_update_last_action, $column, $seasonId = null)
    {
        if ($model && $column && $should_update_last_action) {

            $field = 'competition_id';

            $jobName = class_basename($this) . '-' . $this->jobId;
            $model_id = $model->id;
            $seasonInfo = $seasonId ? " - Season #{$seasonId}" : '';
            Log::channel($this->channel)->info("{$jobName}: ***UpdateLastAction ({$column}) for Compe #{$model_id}{$seasonInfo}");


            try {
                DB::transaction(function () use ($model, $column, $field, $seasonId) {
                    // Always use hasMany relationship now
                    $query = $model->lastActions()->where($field, $model->id);

                    if ($seasonId !== null) {
                        $query->where('season_id', $seasonId);
                    } else {
                        $query->whereNull('season_id');
                    }

                    $lastAction = $query->first();

                    if ($lastAction) {
                        $lastAction->update([$column => now()]);
                    } else {
                        $model->lastActions()->create([
                            $field      => $model->id,
                            'season_id' => $seasonId,
                            $column     => now(),
                        ]);
                    }

                    $qry = $model->lastActions()->where('season_id', null)->first();
                    if ($qry) {
                        $commonData = [];
                        $commonData['abbreviations_last_fetch'] = $qry->abbreviations_last_fetch;
                        $commonData['seasons_last_fetch'] = $qry->seasons_last_fetch;
                        $model->lastActions()->update($commonData);
                    }
                });
            } catch (\Exception $e) {
                Log::channel($this->channel)->error("Failed to update last action: " . $e->getMessage());
            }
        }
    }

    /**
     * Updates or creates the last action entry for the given model and column.
     *
     * @param mixed $model The model to update the last action for.
     * @param mixed $should_update_last_action Whether to update the last action.
     * @param string $column The column to update.
     */
    private function updateGameLastAction($model, $should_update_last_action, $column)
    {
        if ($model && $column && $should_update_last_action) {

            $field = 'game_id';

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
        // Calculate elapsed time
        $elapsedTime = time() - $this->startTime;

        if ($elapsedTime >= $this->maxExecutionTime) {
            // Convert elapsed time to seconds or minutes
            $formattedTime = round($elapsedTime / 60) . ' mins';

            // Get the class name dynamically
            $jobName = class_basename($this) . '-' . $this->jobId;

            $msg = "{$jobName}: ***Script execution time exceeded ({$formattedTime}). Terminating...";

            Log::channel($this->channel)->info($msg);
            echo $msg . "\n";

            return true;
        }
        return false;
    }

    public function timeTaken($seconds_taken): string
    {
        return round($seconds_taken) . " secs";
    }

    /**
     * Get a random delay for competitions, between 10 and $delayCompetitions.
     *
     * @return int
     */
    public function getRequestDelayCompetitions(): int
    {
        return rand(5, $this->delayCompetitions);
    }

    /**
     * Get a random delay for seasons, between 10 and $delaySeasons.
     *
     * @return int
     */
    public function getRequestDelaySeasons(): int
    {
        return rand(5, $this->delaySeasons);
    }

    /**
     * Get a random delay for games, between 10 and $delayGames.
     *
     * @return int
     */
    public function getRequestDelayGames(): int
    {
        return rand(5, $this->delayGames);
    }

    protected array $taskKeyMap = [
        'competitionAbbreviationsFetch' => 'abbreviations_last_fetch',
        'seasonsFetch'                   => 'seasons_last_fetch',
        'standingsRecentResults'         => 'standings_recent_results_last_fetch',
        'standingsHistoricalResults'     => 'standings_historical_results_last_fetch',
        'matchesRecentResults'           => 'matches_recent_results_last_fetch',
        'matchesHistoricalResults'       => 'matches_historical_results_last_fetch',
        'matchesFixtures'                => 'matches_fixtures_last_fetch',
        'matchesShallowFixtures'         => 'matches_shallow_fixtures_last_fetch',
        'matchRecentResults'             => 'match_recent_results_last_fetch',
        'matchHistoricalResults'         => 'match_historical_results_last_fetch',
        'matchFixtures'                  => 'match_fixtures_last_fetch',
        'matchShallowFixtures'           => 'match_shallow_fixtures_last_fetch',
        'oddRecentResults'               => 'odd_recent_results_last_fetch',
        'oddHistoricalResults'           => 'odd_historical_results_last_fetch',
        'oddFixtures'                    => 'odd_fixtures_last_fetch',
        'oddShallowFixtures'             => 'odd_shallow_fixtures_last_fetch',
        'predictionsTrain'               => 'predictions_last_train',
        'predictionsTrainedTo'           => 'predictions_trained_to',
        'predictionsDone'                => 'predictions_last_done',
        'competitionStatsDone'           => 'stats_last_done',
        'predictionsStatsDone'           => 'predictions_stats_last_done',
    ];

    /**
     * Prepare a standardized Pusher payload for this job.
     *
     * @param string $state e.g. 'started', 'completed'
     * @return array
     */
    protected function getJobBroadcastPayload(string $state): array
    {
        // Get base job name without "Handler" suffix
        $jobName = Str::camel(Str::before(class_basename($this), 'Handler'));
        // Map the task to a standardized key
        $actionKey = $this->taskKeyMap[$jobName.Str::studly($this->task)] ?? 'unknown_task';

        return [
            'status' => $state,
            'message' => $jobName . ' job ' . $state,
            'results' => [
                'actionKey'     => $actionKey,
                'competitionId' => $this->competitionId ?? 'all',
                'jobId'         => $this->jobId,
            ],
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
