<?php

namespace App\Jobs\Automation;

use App\Jobs\Automation\Traits\AutomationTrait;
use App\Jobs\Automation\Traits\PredictionAutomationTrait;
use App\Models\Competition;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class TrainPredictionsHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait, PredictionAutomationTrait;

    protected $preferSavedMatches = false;
    protected $isGridSearch = true;
    protected $isRandomSearch = true;
    protected $target;
    protected $modelType;
    protected $client;
    protected $matchesCounts;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $jobId, $lastActionDelay = false, $competitionId = null, $seasonId = null, $options = [])
    {

        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 120;
        $this->startTime = now();

        $this->initializeSettings();

        // Instantiate the context class for handling game sources
        $this->sourceContext = new GameSourceStrategy();

        // Set the initial game source strategy (can be switched dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());

        // Set the jobID
        $this->jobId = $jobId ?? str()->random(6);

        // Set the task property
        $this->task = $task ?? 'current_predictions';

        if (is_numeric($lastActionDelay)) {
            $this->lastActionDelay = $lastActionDelay;
        }

        if ($competitionId) {
            $this->competitionId = $competitionId;
            request()->merge(['competition_id' => $competitionId]);
        }

        if ($seasonId) {
            $this->seasonId = $seasonId;
            request()->merge(['season_id' => $seasonId]);
        }

        if (isset($options['prefer_saved_matches']) && $options['prefer_saved_matches']) {
            $this->preferSavedMatches = $options['prefer_saved_matches'];
        }

        if (isset($options['is_grid_search']) && $options['is_grid_search']) {
            $this->isGridSearch = $options['is_grid_search'];
        }

        if (isset($options['is_random_search']) && $options['is_random_search']) {
            $this->isRandomSearch = $options['is_random_search'];
        }

        if (isset($options['predictor_url']) && $options['predictor_url']) {
            $this->predictorUrl = $options['predictor_url'];
        }

        if (isset($options['target']) && $options['target']) {
            $this->target = $options['target'];
        }

        if (isset($options['modelType']) && !empty($options['modelType'])) {
            $this->modelType = $options['modelType'];
        }
    }

    /**
     * Execute the job to fetch matches for competitions.
     */
    public function handle(): void
    {

        $this->matchesCounts = 1000;
        request()->merge(['prediction_type' => 'regular_prediction_12_6_4_' . $this->matchesCounts]);

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $this->lastActionColumn = 'predictions_last_train';

        // Set delay in minutes based on the task type:
        // Default case for train 3 months
        if (!is_numeric($this->lastActionDelay) && $this->lastActionDelay >= 0) {
            $this->lastActionDelay = 60 * 24 * 90;
        }

        // Fetch competitions that need season data updates
        $competitions = $this->getCompetitions();

        // Process competitions to calculate action counts and log job details
        $actionCounts = $competitions->count();
        $competition_counts = $competitions->count();
        $this->logAndBroadcastJobLifecycle('START', $competitions);
        $this->automationInfo("Predictor URL: {$this->predictorUrl}");

        // loggerModel competition_counts and Action Counts
        $this->trainPredictionsLoggerModel(true, $competition_counts, $actionCounts);

        $total = $competitions->count();
        $should_sleep = false;
        $this->client = new Client();
        $should_exit = false;

        foreach ($competitions as $key => $competition) {
            if ($should_exit) {
                break;
            }

            // Collect season IDs as array
            $seasonIds = $competition->seasons->pluck('id')->all();
            // Format season IDs for logging
            $seasonIdsString = empty($seasonIds) ? 'none' : implode(',', $seasonIds);
            $this->automationInfo(($key + 1) . "/{$total}. Competition: #{$competition->id}, Seasons: [{$seasonIdsString}] ({$competition->country->name} - {$competition->name})");

            $seasons = $competition->seasons;
            $limit = $this->task == 'historical_predictions' ? 15 : 1;

            $results = $this->workOnSeasons($competition, $seasons, $limit);
            $should_sleep = $results['should_sleep'];
            $should_exit = $results['should_exit'];

            // Increment Completed Competition Counts
            $this->incrementCompletedCompetitionCounts('trainPredictionsLoggerModel');
            $this->automationInfo("------------");

            // Introduce a delay to avoid rapid consecutive requests
            if ($should_sleep && $competition_counts > 0) {
                sleep($this->getRequestDelayCompetitions());
            }
        }

        if ($this->competitionId && $competitions->count() === 0) {
            $this->updateCompetitionLastAction($this->getCompetition(), true, $this->lastActionColumn);
        }

        $this->logAndBroadcastJobLifecycle('END');
    }

    private function workOnSeason($competition, $season)
    {
        $results = [
            'should_exit'  => false,
            'should_sleep' => false,
            'has_errors'   => false,
        ];

        $delay = 60 * 24 * 180;

        while (!is_connected()) {
            $this->automationInfo("  Offline. Retrying in 10 secs...");
            sleep(10);
        }

        $job = $competition->predictionJobs()->create([
            'process_id'   => Str::random(),
            'status'       => 'pending',
            'available_at' => time() + 60,
            'created_at'   => time(),
        ]);

        $options = [
            'competition'                     => $competition->id,
            'season_id'                       => $season->id,
            'season_start_date'               => $season->start_date,
            'job_id'                          => (string) $job->id,
            'target'                          => $this->target,
            'prefer_saved_matches'            => $this->preferSavedMatches,
            'is_grid_search'                  => $this->isGridSearch,
            'is_random_search'                => $this->isRandomSearch,
            'retrain_if_last_train_is_before' => now()->subMinutes($delay)->toDateString(),
            'ignore_trained'                  => true,
            'per_page'                        => $this->matchesCounts,
            'model_type'                      => $this->modelType,
        ];

        try {
            $this->client->post($this->predictorUrl . '/train', ['json' => $options]);
            $results['should_sleep'] = true;

            if (!$this->isJobAcknowledged($job->id, 15)) {
                $this->automationInfo("***Job {$job->id} was not acknowledged in time.");
                $results['should_exit'] = true;
            } else {
                $results = $this->pollJobCompletion($competition, $season, $job->id, $options);
            }
        } catch (\Exception $e) {
            $results['has_errors'] = true;
            $this->automationInfo("***Error in Season {$season->id}: {$e->getMessage()}");
        }

        return $results;
    }

    /**
     * Function to poll and check if the job is completed.
     */
    private function pollJobCompletion($competition, $season, $jobId, $options)
    {
        $results = [
            'should_exit'  => false,
            'should_sleep' => false,
            'has_errors'   => false,
        ];

        $startTime = $this->startTime;
        // Capture start time
        $requestStartTime = microtime(true);

        $maxWaitTime = $this->maxExecutionTime; // Max wait time in secs
        $checkInterval = 30; // Poll every x secs
        $totalPolls = ceil($maxWaitTime / $checkInterval); // Calculate total polls

        $i = 0;
        while (now()->diffInSeconds($startTime) < $maxWaitTime) {
            $i++;

            // Check if the process ID status is marked as "completed"
            $jobStatus = Competition::find($competition->id)
                ->predictionJobs()
                ->where('id', $jobId)
                ->first();

            // Log the polling attempt message
            $this->logPollingAttempt($i, $totalPolls, $startTime, $maxWaitTime);

            if ($jobStatus && $jobStatus->status == 'completed') {
                $this->automationInfo("***Job ID #{$jobId} marked as completed {$jobStatus->finished_at->diffForHumans()}.");

                $checked_last_action = $this->getLastAction($competition, $season->id);
                $lastActionTime = 'N/A';
                if ($checked_last_action) {
                    $lastActionTime = Carbon::parse($checked_last_action)->diffForHumans();
                }

                // Capture end time and calculate time taken
                $requestEndTime = microtime(true);
                $seconds_taken = intval($requestEndTime - $requestStartTime);

                $this->automationInfo("***Time taken working on Compe: #{$competition->id}, Season: #{$season->id} " . $this->timeTaken($seconds_taken) . ", new Last Action: {$lastActionTime}.\n");
                $results['should_sleep'] = true;
                break; // Exit the loop since training is completed
            }

            // Sleep for the check interval before checking again
            sleep($checkInterval);
        }

        // Log timeout if training did not complete
        if (now()->diffInSeconds($startTime) >= $maxWaitTime) {
            $this->automationInfo("***Timeout: Training for Competition #{$competition->id} did not complete within the expected time.");
            $results['should_exit'] = true;
        }

        $this->updateCompetitionLastAction($competition, !$results['should_exit'], $this->lastActionColumn, $options['season_id']);

        return $results;
    }

    private function lastActionFilters($query)
    {
        // Conditionally filter games based on the task being performed
        $query = $query->when($this->task == 'task', function ($q) {
            $q->where('utc_date', '<', Carbon::now()->subDays(5));
        });

        return $query;
    }

    private function getCompetitions()
    {
        $seasonsClause = fn($q) => $q->when(
            $this->task == 'current_predictions',
            fn($q) => $q->where('is_current', true),
            fn($q) => $q->where('is_current', false)
        );

        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->where('competitions.games_per_season', '>', 0)
            ->when(!request()->ignore_status, fn($q) => $q->where('competitions.status_id', activeStatusId()))
            ->when($this->competitionId, fn($q) => $q->where('competitions.id', $this->competitionId))
            ->when(
                $this->seasonId,
                fn($q) => $q->where('competition_last_actions.season_id', $this->seasonId),
                fn($q) => $q->whereNull('competition_last_actions.season_id')
            )
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->select('competitions.*')
            ->limit(1000)
            ->with(['seasons' => fn($q) => $this->seasonsFilter($q, $seasonsClause)])
            ->orderBy('competition_last_actions.' . $this->lastActionColumn, 'asc')
            ->get();

        return $competitions;
    }
}
