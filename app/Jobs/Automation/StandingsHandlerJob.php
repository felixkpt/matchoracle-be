<?php

namespace App\Jobs\Automation;

use App\Jobs\Automation\Traits\AutomationTrait;
use App\Models\Competition;
use App\Models\FailedStandingLog;
use App\Models\StandingJobLog;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class StandingsHandlerJob implements ShouldQueue
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
    }

    /**
     * Execute the job to fetch standings for competitions.
     */
    public function handle(): void
    {

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $lastFetchColumn = 'standings_' . $this->task . '_last_fetch';

        $delay = 60 * 24 * 1;
        if ($this->ignoreTiming) {
            $delay = 0;
        }

        // Fetch competitions that need season data updates
        $competitions = $this->getCompetitions($lastFetchColumn, $delay);

        // Process competitions to calculate action counts and log job details
        $actionCounts = 0;
        foreach ($competitions as $key => $competition) {
            $seasons = $competition->seasons;
            $total_seasons = $seasons->count();
            $actionCounts += $total_seasons;
        }

        $competition_counts = $competitions->count();
        $this->logAndBroadcastJobLifecycle('START', $competitions);
        // loggerModel competition_counts and Action Counts

        $this->loggerModel(true, $competition_counts, $actionCounts);

        // Loop through each competition to fetch and update standings
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) {
                break;
            }

            $this->automationInfo(($key + 1) . "/{$total}. Competition: #{$competition->id}, Season: #{$this->seasonId}, ({$competition->country->name} - {$competition->name})");

            $seasons = $competition->seasons;
            $total_seasons = $seasons->count();

            $should_sleep_for_seasons = false;
            $should_update_last_action = false;
            foreach ($seasons as $season_key => $season) {

                if ($this->runTimeExceeded()) {
                    $should_exit = true;
                    break;
                }

                $start_date = Str::before($season->start_date, '-');
                $end_date = Str::before($season->end_date, '-');

                $this->automationInfo("***" . ($season_key + 1) . "/{$total_seasons}. Season #{$season->id} ({$start_date}/{$end_date})");

                while (!is_connected()) {
                    $this->automationInfo("You are offline. Retrying in 10 secs...");
                    sleep(10);
                }

                // Capture start time
                $requestStartTime = microtime(true);

                // Obtain the specific handler for fetching standings based on the game source strategy
                $standingsHandler = $this->sourceContext->standingsHandler();

                // Fetch standings for the current competition
                $data = $standingsHandler->fetchStandings($competition->id, $season->id);

                // Output the fetch result for logging
                $this->automationInfo($data['message'] . "");

                // Capture end time and calculate time taken
                $requestEndTime = microtime(true);
                $seconds_taken = intval($requestEndTime - $requestStartTime);

                // Log time taken for this game request
                $this->automationInfo("***Time taken working on  Compe #{$competition->id} - season #{$season->id}: " . $this->timeTaken($seconds_taken));

                $data['seconds_taken'] = $seconds_taken;

                if ($total_seasons > 1) {
                    $should_sleep_for_competitions = true;
                    $should_sleep_for_seasons = true;
                }

                $should_update_last_action = true;

                $this->doLogging($data);

                if ($data['status'] === 504) {
                    $should_exit = true;
                    $should_update_last_action = false;
                    break;
                }

                // Introduce a delay to avoid rapid consecutive requests
                sleep($should_sleep_for_seasons ? $this->getRequestDelaySeasons() : 0);
                $should_sleep_for_seasons = false;

                $this->updateCompetitionLastAction($competition, $should_update_last_action, $lastFetchColumn, $season->id);
            }

            // Increment Completed Competition Counts
            $this->incrementCompletedCompetitionCounts();
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

    private function applyRecentResultsFilter(Builder $query): Builder
    {
        return $query->whereHas('games', function ($subQuery) {
            $subQuery->where('utc_date', '>=', Carbon::now()->subDays(5))
                ->where('utc_date', '<', Carbon::now())
                ->where('game_score_status_id', gameScoresStatus('scheduled'));
        });
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

            if ($failed_counts || ($data && $data['status'] == 500)) $this->logFailure(new FailedStandingLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false, $competition_counts = null, $action_counts = null)
    {
        if ($this->competitionId) {
            return;
        }

        $task = $this->task;
        $today = Carbon::now()->format('Y-m-d');
        $record = StandingJobLog::where('task', $task)->where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

        if (!$record) {

            $arr = [
                'source_id' => $this->sourceContext->getId(),
                'task' => $task,
                'date' => $today,
                'job_run_counts' => 1,
                'competition_counts' => $competition_counts,
                'action_counts' => $action_counts,
            ];

            $record = StandingJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }

    private function seasonsFilter($competitionQuery)
    {
        return $competitionQuery
            ->when($this->seasonId, fn($q) => $q->where('id', $this->seasonId))
            ->where('fetched_standings', false)
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
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->when($this->task == 'recent_results', fn($q) => $this->applyRecentResultsFilter($q))
            ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->where('competitions.has_standings', true)
            ->select('competitions.*')
            ->limit(1000)
            ->with(['seasons' => fn($q) => $this->seasonsFilter($q)])
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
            ->get();
    }
}
