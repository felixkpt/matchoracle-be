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

class PredictionsHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait, PredictionAutomationTrait;

    /**
     * Job details.
     *
     * @property string $jobId          The unique identifier for the job.
     * @property string $task           The type of task to be performed by the job (default is 'train').
     * @property bool   $ignore_timing  Whether to ignore timing constraints for the job.
     * @property int    $competition_id The identifier for the competition associated with the job.
     */
    protected $jobId;
    protected $task = 'train';
    protected $ignoreTiming;
    protected $competitionId;
    protected $target;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $job_id, $ignore_timing = false, $competition_id = null, $options = [])
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
        $this->jobId = $job_id ?? str()->random(6);

        // Set the task property
        if ($task) {
            $this->task = $task;
        }

        if ($ignore_timing) {
            $this->ignoreTiming = $ignore_timing;
        }

        if ($competition_id) {
            $this->competitionId = $competition_id;
            request()->merge(['competition_id' => $competition_id]);
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
        if ($this->ignoreTiming) $delay = 0;

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))
            ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->where(fn($query) => $query->whereNotNull('competition_last_actions.predictions_last_train'))
            ->select('competitions.*')
            ->limit(1000)
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc');

        // Process competitions to calculate action counts and log job details
        $actionCounts = $competitions->count();
        $competition_counts = $competitions->count();
        $competitions = $competitions->when(request()->competition_id, fn($q) => $q->where('competitions.id', request()->competition_id))->get();
        $this->jobStartEndLog('START', $competitions);
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
            if ($should_exit) break;

            if ($this->runTimeExceeded()) {
                $should_exit = true;
                break;
            }

            $compe_run_start_time = Carbon::now();
            // create a jobs table entity
            $processId = Str::random();
            $job = $competition->jobs()->create([
                'process_id' => $processId,
                'status' => 'processing',
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
            $this->updateLastAction($this->getCompetition(), true, $lastFetchColumn);
        }

        $this->jobStartEndLog('END');
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
                ->jobs()
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

        $this->updateLastAction($competition, $jobStatus == 'completed', $lastFetchColumn);
    }

    private function lastActionFilters($query)
    {
        // Conditionally filter games based on the task being performed
        $query = $query->when($this->task == 'task', function ($q) {
            $q->where('utc_date', '<', Carbon::now()->subDays(5));
        });

        return $query;
    }
}
