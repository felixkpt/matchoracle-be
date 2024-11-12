<?php

namespace App\Jobs\Automation;

use App\Jobs\Automation\Traits\AutomationTrait;
use App\Models\Competition;
use App\Models\FailedMatchesLog;
use App\Models\MatchesJobLog;
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

class MatchesHandlerJob implements ShouldQueue
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
     * Execute the job to fetch matches for competitions.
     */
    public function handle(): void
    {

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true, 'shallow_fetch' => $this->task == 'shallow_fixtures']);

        $lastFetchColumn = 'matches_' . $this->task . '_last_fetch';

        // Set delay in minutes based on the task type:
        $delay = $this->getDelay();
        if ($this->ignore_timing) $delay = 0;

        // Get competitions that need matches data updates
        $competitions = $this->getCompetitions($lastFetchColumn, $delay);

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

        // Loop through each competition to fetch and update matches
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) break;

            $last_action = $competition->lastAction->{$lastFetchColumn} ?? 'N/A';

            $this->automationInfo(($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name}) | Last fetch {$last_action} ");

            $seasons = $this->seasonsFilter($competition);
            $total_seasons = $seasons->count();

            $should_sleep_for_seasons = false;
            foreach ($seasons as $season_key => $season) {

                if ($this->runTimeExceeded()) {
                    $should_exit = true;
                    break;
                }

                if ($season_key >= ($this->task == 'historical_results' ? 15 : 1)) break;

                $start_date = Str::before($season->start_date, '-');
                $end_date = Str::before($season->end_date, '-');

                $this->automationInfo("***".($season_key + 1) . "/{$total_seasons}. Season #{$season->id} ({$start_date}/{$end_date})");

                [$should_sleep_for_competitions, $should_sleep_for_seasons, $should_exit] = $this->workOnSeason($competition, $season, $lastFetchColumn);

                // Introduce a delay to avoid rapid consecutive requests
                sleep($should_sleep_for_seasons ? $this->getRequestDelaySeasons() : 0);
                $should_sleep_for_seasons = false;
            }

            $this->determineCompetitionGamesPerSeason($competition, $seasons);

            // Increment Completed Competition Counts
            $this->incrementCompletedCompetitionCounts();
            $this->automationInfo("------------");

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? $this->getRequestDelayCompetitions() : 0);

            $should_sleep_for_competitions = false;
        }

        $this->jobStartEndLog('END');
    }


    private function getDelay(): int
    {
        switch ($this->task) {
            case 'historical_results':
                return 60 * 24;
            case 'shallow_fixtures':
                return 60 * 24;
            case 'fixtures':
                return 60 * 24 * 4;
            case 'recent_results':
                return 60;
            default:
                return 60 * 24 * 2;
        }
    }

    private function getCompetitions($lastFetchColumn, $delay)
    {
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->whereHas('seasons')
            ->when($this->task == 'recent_results', fn($q) => $this->applyRecentResultsFilter($q))
            ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(1000)
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc');

        return $competitions;
    }

    function applyRecentResultsFilter(Builder $query): Builder
    {
        return $query->whereHas('games', function ($q) {
            $q->where('utc_date', '>', Carbon::now()->subDays(5))
                ->where('utc_date', '<', Carbon::now()->subHours(5));
        });
    }

    private function workOnSeason($competition, $season, $lastFetchColumn)
    {
        $should_sleep_for_competitions = false;
        $should_sleep_for_seasons = false;
        $should_exit = false;

        while (!is_connected()) {
            $this->automationInfo("You are offline. Retrying in 10 secs...");
            sleep(10);
        }

        // Capture start time
        $requestStartTime = microtime(true);

        // Obtain the specific handler for fetching matches based on the game source strategy
        $matchesHandler = $this->sourceContext->matchesHandler();

        // Fetch matches for the current competition
        $data = $matchesHandler->fetchMatches($competition->id, $season->id, Str::endsWith($this->task, 'fixtures'));

        // Output the fetch result for logging
        $this->automationInfo("***" . $data['message'] . "");

        // Capture end time and calculate time taken
        $requestEndTime = microtime(true);
        $seconds_taken = intval($requestEndTime - $requestStartTime);

        // Log time taken for this game request
        $this->automationInfo("***Time taken working on Compe #{$competition->id} - season #{$season->id}: " . $this->timeTaken($seconds_taken));

        $data['seconds_taken'] = $seconds_taken;

        $should_sleep_for_competitions = true;
        $should_sleep_for_seasons = true;
        $should_update_last_action = true;

        $this->doLogging($data);

        if ($data['status'] === 504) {
            $should_exit = true;
            $should_update_last_action = false;
        }

        $this->updateLastAction($competition, $should_update_last_action, $lastFetchColumn);

        return [$should_sleep_for_competitions, $should_sleep_for_seasons, $should_exit];
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

            if ($failed_counts || ($data && isset($data['status']) && $data['status'] == 500)) $this->logFailure(new FailedMatchesLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false, $competition_counts = null, $action_counts = null)
    {
        $task = $this->task;
        $today = Carbon::now()->format('Y-m-d');
        $record = MatchesJobLog::where('task', $task)->where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

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

            $record = MatchesJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }

    private function seasonsFilter($competition)
    {
        return $competition->seasons()
            ->when(Str::endsWith($this->task, 'fixtures'), fn($q) => $q->where('is_current', true))
            ->whereDate('start_date', '>=', $this->historyStartDate)
            ->where('fetched_all_matches', false)
            ->orderBy('start_date', 'asc')
            ->get();
    }

    private function determineCompetitionGamesPerSeason($competition, $seasons)
    {
        if ($this->task == 'fixtures') return false;

        $season_matches_arr = [];

        foreach ($seasons as $season) {
            $teams_counts = $competition->teams()
                ->wherePivot('season_id', $season->id)
                ->count();

            $expected_games_per_season = intval(2 * ($teams_counts - 1) * ($teams_counts / 2));

            if ($expected_games_per_season === 0) continue;

            $this->automationInfo("***Season ID: {$season->id}, Teams counts: {$teams_counts}, Expected games per season: {$expected_games_per_season}");

            $start_date = Str::before($season->start_date, '-');
            $end_date = Str::before($season->end_date, '-');

            $season_games = $season->games()->count();
            $season_matches_arr[] = $season_games;

            $this->automationInfo("***Season #{$season->id} ({$start_date}/{$end_date}, {$season_games} games)");
        }

        // season average matches is count of most repeated match counts
        rsort($season_matches_arr);
        $season_matches_arr = array_filter($season_matches_arr, fn($val) => $val >= $expected_games_per_season);

        $this->automationInfo("***Counts after filtering >= expected_games_per_season: " . count($season_matches_arr) . "");

        if (count($season_matches_arr) >= 3) {
            // Get the first three most repeated counts
            $most_repeated_counts = array_slice($season_matches_arr, 0, 3);

            $games_per_season = intval(array_sum($most_repeated_counts) / 3);
            // Check if the first two counts are the same
            if (count(array_count_values($most_repeated_counts)) == 1 || $games_per_season == $expected_games_per_season) {
                if ($games_per_season > 0 && $competition->games_per_season != $games_per_season) {
                    $this->automationInfo("***Games per season: {$games_per_season} games");

                    $competition->games_per_season = $games_per_season;
                    $competition->save();
                }
            } else {
                $this->automationInfo("***Games per season: could not be determined");
            }
        }
    }
}
