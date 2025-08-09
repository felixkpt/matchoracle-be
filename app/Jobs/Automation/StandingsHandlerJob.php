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

    /**
     * Create a new job instance.
     */
    public function __construct($task, $job_id, $ignore_timing = false, $competition_id = null)
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
    }

    /**
     * Execute the job to fetch standings for competitions.
     */
    public function handle(): void
    {

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $lastFetchColumn = 'standings_' . $this->task . '_last_fetch';

        $delay = 60 * 24 * 15;
        if ($this->ignoreTiming) $delay = 0;

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))
            ->whereHas('gameSources', function ($query) {
                $query->where('game_source_id', $this->sourceContext->getId());
            })
            ->whereHas('seasons')
            ->when($this->task == 'recent_results', fn($q) => $this->applyRecentResultsFilter($q))
            ->where('has_standings', true)
            ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(1000)
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc');

        // Process competitions to calculate action counts and log job details
        $actionCounts = 0;
        foreach ((clone $competitions)->get() as $key => $competition) {
            $seasons = $this->seasonsFilter($competition);
            $total_seasons = $seasons->count();
            $actionCounts += $total_seasons;
        }

        $competition_counts = $competitions->count();
        $competitions = $competitions->when(request()->competition_id, fn($q) => $q->where('competitions.id', request()->competition_id))->get();
        $this->jobStartEndLog('START', $competitions);
        // loggerModel competition_counts and Action Counts
        $this->loggerModel(true, $competition_counts, $actionCounts);

        // Loop through each competition to fetch and update standings
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) break;

            $this->automationInfo($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})";

            $seasons = $this->seasonsFilter($competition);
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

                // recheck if compe has_standings
                if (!Competition::find($competition->id)->has_standings) break;

                // Output the fetch result for logging
                $this->automationInfo($data['message'] . "");

                // Capture end time and calculate time taken
                $requestEndTime = microtime(true);
                $seconds_taken = intval($requestEndTime - $requestStartTime);

                // Log time taken for this game request
                $this->automationInfo("***Time taken working on  Compe #{$competition->id} - season #{$season->id}: " . $this->timeTaken($seconds_taken));

                $data['seconds_taken'] = $seconds_taken;

                $should_sleep_for_competitions = true;
                $should_sleep_for_seasons = true;
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
            }

            $this->updateLastAction($competition, $should_update_last_action, $lastFetchColumn);

            // Increment Completed Competition Counts
            $this->incrementCompletedCompetitionCounts();
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

    private function seasonsFilter($competition)
    {
        return $competition->seasons()
            ->where('fetched_standings', false)
            ->orderBy('start_date', 'desc')->get();
    }

    function applyRecentResultsFilter(Builder $query): Builder
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
        if ($this->competitionId) return;

        $task = $this->task;
        $today = Carbon::now()->format('Y-m-d');
        $record = StandingJobLog::where('task', $task)->where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

        if (!$record) {
            if ($competition_counts <= 0) {
                abort(422, 'Competition counts is needed');
            }

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
}
