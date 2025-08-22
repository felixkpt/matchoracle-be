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
     * Create a new job instance.
     */
    public function __construct($task, $jobId, $ignoreTiming = false, $competitionId = null, $seasonId = null)
    {
        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 10;
        $this->startTime = time();

        $this->initializeSettings();

        // Instantiate the context class for handling game sources
        $this->sourceContext = new GameSourceStrategy();

        // Set the initial game source strategy (can be switched dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());

        // Set the jobID
        $this->jobId = $jobId ?? str()->random(6);

        // Set the task property
        $this->task = $task ?? 'run';

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
    }

    /**
     * Execute the job to fetch seasons for competitions.
     */
    public function handle(): void
    {

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $this->lastFetchColumn = 'seasons_last_fetch';

        $delay = 60 * 24 * 30;
        if ($this->ignoreTiming) {
            $delay = 0;
        }

        // Fetch competitions that need season data updates
        $competitions = $this->getCompetitions($delay);

        // Process competitions to calculate action counts and log job details
        $actionCounts = $competitions->count();
        $competition_counts = $competitions->count();
        $this->logAndBroadcastJobLifecycle('START', $competitions);
        // loggerModel competition_counts and Action Counts
        $this->loggerModel(true, $competition_counts, $actionCounts);

        // Loop through each competition to fetch and update seasons
        $total = $competitions->count();
        $should_exit = false;

        foreach ($competitions as $key => $competition) {
            if ($should_exit) {
                break;
            }
            
            if ($key >= 15) {
                break;
            }

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

            $this->updateCompetitionLastAction($competition, true, $this->lastFetchColumn, $this->seasonId);

            // Increment Completed Competition Counts
            $this->incrementCompletedCompetitionCounts();
            $this->automationInfo("------------");

            $this->doLogging($data);

            $should_sleep_for_competitions = true;

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? $this->getRequestDelayCompetitions() : 0);
            $should_sleep_for_competitions = false;
        }

        if ($this->competitionId && $competitions->count() === 0) {
            $this->updateCompetitionLastAction($this->getCompetition(), true, $this->lastFetchColumn, $this->seasonId);
        }

        $this->logAndBroadcastJobLifecycle('END');
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
                'run_action_counts' => (int) $run_action_counts,
                'average_seconds_per_action' => (int) $newAverageSeconds,
                'created_counts' => (int) $exists->created_counts + $created_counts,
                'updated_counts' => (int) $exists->updated_counts + $updated_counts,
                'failed_counts' => (int) $exists->failed_counts + $failed_counts,
            ];

            $exists->update($arr);

            if ($failed_counts || ($data && $data['status'] == 500)) $this->logFailure(new FailedSeasonLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false, $competition_counts = 1, $action_counts = 1)
    {
        if ($this->competitionId) {
            return;
        }

        $today = Carbon::now()->format('Y-m-d');
        $record = SeasonJobLog::where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

        if (!$record) {

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

    private function getCompetitions($delay)
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
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->where(fn($query) => $this->lastActionDelay($query, $this->lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(1000)
            ->orderBy('competition_last_actions.' . $this->lastFetchColumn, 'asc')
            ->get();
    }
}
