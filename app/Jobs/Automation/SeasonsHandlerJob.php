<?php

namespace App\Jobs\Automation;

use App\Jobs\Automation\Traits\AutomationTrait;
use App\Models\Competition;
use App\Models\FailedSeasonLog;
use App\Models\SeasonJobLog;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SeasonsHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * Job details.
     *
     * @property string $jobId          The unique identifier for the job.
     * @property string $task           The type of task to be performed by the job (default is 'train').
     * @property bool   $ignore_timing  Whether to ignore timing constraints for the job.
     * @property int    $competition_id The identifier for the competition associated with the job.
     */
    protected $jobId;
    protected $task = 'run';
    protected $ignore_timing;
    protected $competition_id;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $job_id, $ignore_timing = false, $competition_id = null)
    {
        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 10;
        $this->startTime = time();

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
            $this->ignore_timing = $ignore_timing;
        }

        if ($competition_id) {
            $this->competition_id = $competition_id;
            request()->merge(['competition_id' => $competition_id]);
        }
    }

    /**
     * Execute the job to fetch seasons for competitions.
     */
    public function handle(): void
    {

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $lastFetchColumn = 'seasons_last_fetch';

        $delay = 60 * 24 * 15;
        if ($this->ignore_timing) $delay = 0;

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))
            ->when(request()->competition_id, fn($q) => $q->where('competitions.id', request()->competition_id))
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(1000)
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc');

        // Process competitions to calculate action counts and log job details
        $actionCounts = $competitions->count();
        $competition_counts = $competitions->count();
        $competitions = $competitions->when(request()->competition_id, fn($q) => $q->where('competitions.id', request()->competition_id))->get();
        $this->jobStartEndLog('START', $competitions);
        // loggerModel competition_counts and Action Counts
        $this->loggerModel(true, $competition_counts, $actionCounts);

        // Loop through each competition to fetch and update seasons
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) break;

            if ($key >= 15) break;

            if ($this->runTimeExceeded()) {
                $should_exit = true;
                break;
            }

            $this->automationInfo(($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})");

            while (!is_connected()) {
                $this->automationInfo("You are offline. Retrying in 10 secs...");
                sleep(10);
            }

            // Capture start time
            $requestStartTime = microtime(true);

            // Obtain the specific handler for fetching seasons based on the game source strategy
            $seasonsHandler = $this->sourceContext->seasonsHandler();

            // Fetch seasons for the current competition
            $data = $seasonsHandler->fetchSeasons($competition->id);

            // Output the fetch result for logging
            $this->automationInfo($data['message'] . "");

            // Capture end time and calculate time taken
            $requestEndTime = microtime(true);
            $seconds_taken = intval($requestEndTime - $requestStartTime);

            // Log time taken for this game request
            $this->automationInfo("Time taken working on  Compe #{$competition->id}: " . $this->timeTaken($seconds_taken));

            $data['seconds_taken'] = $seconds_taken;

            $this->updateLastAction($competition, true, $lastFetchColumn);

            // Increment Completed Competition Counts
            $this->incrementCompletedCompetitionCounts();
            $this->automationInfo("------------");

            $this->doLogging($data);

            $should_sleep_for_competitions = true;

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? $this->getRequestDelayCompetitions() : 0);
            $should_sleep_for_competitions = false;
        }

        $this->jobStartEndLog('END');
    }

    private function doLogging($data = null)
    {
        $created_counts = $data['results']['created_counts'] ?? 0;
        $updated_counts = $data['results']['updated_counts'] ?? 0;
        $failed_counts = $data['results']['failed_counts'] ?? 0;

        $exists = $this->loggerModel();

        if ($exists) {
            $run_action_counts = $exists->run_action_counts + 1;
            $newAverageSeconds = (($exists->average_seconds_per_action * $exists->run_action_counts) + $data['seconds_taken']) / $run_action_counts;

            $arr = [
                'run_action_counts' => $run_action_counts,
                'average_seconds_per_action' => $newAverageSeconds,
                'created_counts' => $exists->created_counts + $created_counts,
                'updated_counts' => $exists->updated_counts + $updated_counts,
                'failed_counts' => $exists->failed_counts + $failed_counts,
            ];

            $exists->update($arr);

            if ($failed_counts || ($data && $data['status'] == 500)) $this->logFailure(new FailedSeasonLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false, $competition_counts = null, $action_counts = null)
    {
        $today = Carbon::now()->format('Y-m-d');
        $record = SeasonJobLog::where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

        if (!$record) {
            if ($competition_counts <= 0) {
                abort(422, 'Competition counts is needed');
            }

            $arr = [
                'source_id' => $this->sourceContext->getId(),
                'date' => $today,
                'job_run_counts' => 1,
                'competition_counts' => $competition_counts,
                'action_counts' => $action_counts,
            ];

            $record = SeasonJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
