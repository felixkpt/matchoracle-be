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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PredictionsHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait, PredictionAutomationTrait;

    protected $target;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $jobId, $ignoreTiming = false, $competitionId = null, $seasonId = null, $options = [])
    {

        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 60;
        $this->startTime = time();

        $this->initializeSettings();

        // Instantiate the context class for handling game sources
        $this->sourceContext = new GameSourceStrategy();

        // Set the initial game source strategy (can be switched dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());

        // Set the jobID
        $this->jobId = $jobId ?? str()->random(6);

        // Set the task property
        $this->task = $task ?? 'train';

        if ($ignoreTiming) {
            $this->ignoreTiming = $ignoreTiming;
        }

        if ($competitionId) {
            $this->competitionId = $competitionId;
            request()->merge(['competition_id' => $competitionId]);
        }

        if ($seasonId) {
            $this->seasonId = $seasonId;
            request()->merge(['season_id' => $seasonId]);
        }
        if (isset($options['predictor_url']) && $options['predictor_url']) {
            $this->predictorUrl = $options['predictor_url'];
        }

        if (isset($options['target']) && $options['target']) {
            $this->target = $options['target'];
        }
    }

    /**
     * Execute the job to fetch matches for competitions.
     */
    public function handle(): void
    {

        $per_page = 1000;
        request()->merge(['prediction_type' => 'regular_prediction_12_6_4_' . $per_page]);

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $lastFetchColumn = 'predictions_last_done';

        // Set delay in minutes based on the task type:
        // Default case for prediction 3 days
        $delay = 60 * 24 * 3;
        if ($this->ignoreTiming) {
            $delay = 0;
        }

        // Fetch competitions that need season data updates
        $competitions = $this->getCompetitions($lastFetchColumn, $delay);

        // Process competitions to calculate action counts and log job details
        $actionCounts = $competitions->count();
        $competition_counts = $competitions->count();
        $this->logAndBroadcastJobLifecycle('START', $competitions);
        $this->automationInfo("Predictor URL: {$this->predictorUrl}");

        // loggerModel competition_counts and Action Counts
        $this->predictionsLoggerModel(true, $competition_counts, $actionCounts);

        // predict for last 3 months plus 10 days from today
        $fromDate = Carbon::today()->subDays(30 * 3)->format('Y-m-d');
        $toDate = Carbon::today()->addDays(10)->format('Y-m-d');

        // Loop through each competition to fetch and update matches
        $options = [
            'target' => null,
            // 'target' => 'bts',
            'last_predict_date' => null,
            'prediction_type' => request()->prediction_type,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'target_match' => null,
        ];

        $total = $competitions->count();
        $should_sleep_for_competitions = false;
        $client = new Client();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) {
                break;
            }

            if ($this->runTimeExceeded()) {
                $should_exit = true;
                break;
            }

            $compe_run_start_time = Carbon::now();
            // create a jobs table entity
            $processId = Str::random();
            $job = $competition->predictionJobs()->create([
                'process_id'   => $processId,
                'status'       => 'pending',
                'available_at' => time() + 60,
                'created_at'   => time(),
            ]);
            
            $last_action = $competition->lastAction->{$lastFetchColumn} ?? 'N/A';
            $this->automationInfo(sprintf(
                "%d/%d [Job ID: %s] - Competition: #%d (%s - %s) | Last predicted: %s",
                $key + 1,
                $total,
                $job->id,
                $competition->id,
                $competition->country->name,
                $competition->name,
                $last_action
            ));

            $options['competition'] = $competition->id;
            $options['job_id'] = (string) $job->id;
            $options['season_id'] = $this->seasonId;

            try {
                $should_update_last_action = true;

                $response = $client->post($this->predictorUrl . '/predict', [
                    'json' => $options
                ]);

                $response->getBody()->getContents();
            } catch (\Exception $e) {
                $should_update_last_action = false;
                $data['status'] = 500;
                $data['message'] = $e->getMessage();
            }

            if ($should_update_last_action) {
                // Call the polling function
                $this->pollJobCompletion($competition, $job->id, $lastFetchColumn, $last_action, $compe_run_start_time, $options);
            } else {
                $this->automationInfo("***No data received, logging skipped.");
            }

            // Increment Completed Competition Counts
            $this->incrementCompletedCompetitionCounts('predictionsLoggerModel');
            $this->automationInfo("------------");

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? $this->getRequestDelayCompetitions() : 0);
            $should_sleep_for_competitions = false;
        }

        if ($this->competitionId && $competitions->count() === 0) {
            $this->updateCompetitionLastAction($this->getCompetition(), true, $lastFetchColumn, $this->seasonId);
        }

        $this->logAndBroadcastJobLifecycle('END');
    }

    /**
     * Function to poll and check if the job is completed.
     */
    private function pollJobCompletion($competition, $jobId, $lastFetchColumn, $last_action, $start_time, $options)
    {
        $startTime = now();
        // Capture start time
        $requestStartTime = microtime(true);

        $maxWaitTime = 60 * 20; // Max wait time in secs
        $checkInterval = 60; // Poll every x secs
        $totalPolls = ceil($maxWaitTime / $checkInterval); // Calculate total polls

        $i = 0;
        $data = [];
        $endTime = Carbon::now();
        $runTime = $endTime->diffInSeconds($start_time);
        $data['seconds_taken'] = $runTime;

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
                $this->automationInfo("***Job ID #{$jobId} marked as completed {$jobStatus->updated_at->diffForHumans()}.");

                $checked_last_action = Competition::find($competition->id)->lastAction->{$lastFetchColumn} ?? null;
                $lastActionTime = 'N/A';

                if ($checked_last_action) {
                    $lastActionTime = Carbon::parse($checked_last_action)->diffForHumans();
                }

                // Capture end time and calculate time taken
                $requestEndTime = microtime(true);
                $seconds_taken = intval($requestEndTime - $requestStartTime);

                $this->automationInfo("***Time taken working on Compe #{$competition->id}: " . $this->timeTaken($seconds_taken) . ", new updated Last Action: {$lastActionTime}.");


                if ($checked_last_action && $checked_last_action != $last_action) {
                }

                break; // Exit the loop since predictioning is completed
            }

            // Sleep for the check interval before checking again
            sleep($checkInterval);
        }

        // Log timeout if predictioning did not complete
        if (now()->diffInSeconds($startTime) >= $maxWaitTime) {
            $this->automationInfo("***Timeout: Prediction for Competition #{$competition->id} did not complete within the expected time.");
        }

        $this->updateCompetitionLastAction($competition, $jobStatus == 'completed', $lastFetchColumn, $options['season_id']);
    }

    private function lastActionFilters($query)
    {
        // Conditionally filter games based on the task being performed
        $query = $query->when($this->task == 'task', function ($q) {
            $q->where('utc_date', '<', Carbon::now()->subDays(5));
        });

        return $query;
    }

    private function seasonsFilter($competitionQuery)
    {
        return $competitionQuery
            ->when($this->seasonId, fn($q) => $q->where('id', $this->seasonId))
            ->when(Str::endsWith($this->task, 'fixtures'), fn($q) => $q->where('is_current', true))
            // ->where('fetched_all_matches', false)
            ->orderBy('start_date', 'asc');
    }

    private function getCompetitions($lastFetchColumn, $delay)
    {
        return Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->where('competitions.games_per_season', '>', 0)
            ->when(!request()->ignore_status, fn($q) => $q->where('competitions.status_id', activeStatusId()))
            ->when($this->competitionId, fn($q) => $q->where('competitions.id', $this->competitionId))
            ->when(
                $this->seasonId,
                fn($q) => $q->where('competition_last_actions.season_id', $this->seasonId),
                fn($q) => $q->whereNull('competition_last_actions.season_id')
            )
            ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->where(fn($query) => $query->whereNotNull('competition_last_actions.predictions_last_train'))
            ->select('competitions.*')
            ->limit(1000)
            ->with(['seasons' => fn($q) => $this->seasonsFilter($q)])
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
            ->get();
    }
}
