<?php

namespace App\Jobs\Automation;

use App\Models\Competition;
use App\Models\FailedMatchLog;
use Illuminate\Support\Str;
use App\Models\MatchJobLog;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MatchHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * Job details.
     *
     * @property string $jobId          The unique identifier for the job.
     * @property string $task           The type of task to be performed by the job (default is 'train').
     * @property bool   $ignore_timing  Whether to ignore timing constraints for the job.
     * @property int    $competition_id The identifier for the competition associated with the job.
     * @property int    $match_id The identifier for the match associated with the job.
     */
    protected $jobId;
    protected $task = 'train';
    protected $ignore_timing;
    protected $competition_id;
    protected $match_id;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $job_id, $ignore_timing = false, $competition_id = null, $match_id = null)
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

        if ($match_id) {
            $this->match_id = $match_id;
        }
    }

    /**
     * Execute the job to fetch matches for competitions.
     */
    public function handle(): void
    {

        $this->loggerModel(true);

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $lastFetchColumn = 'match_' . $this->task . '_last_fetch';

        // Set delay in minutes based on the task type:
        // Default case for historical_results
        $delay = 60 * 24 * 2;
        if ($this->task == 'shallow_fixtures') {
            $delay = 60 * 24;
        } elseif ($this->task == 'fixtures') {
            $delay = 60 * 24 * 4;
        } elseif ($this->task == 'recent_results') {
            $delay = 60;
        }

        if ($this->ignore_timing) $delay = 0;

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))
            ->when(request()->competition_id, fn($q) => $q->where('competitions.id', request()->competition_id))
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->whereHas('games', function ($q) {
                $q->when(!$this->match_id, function ($q) {
                    // Exclude action filters when match_id is not null
                    $q->whereNotIn('game_score_status_id', settledGameScoreStatuses());

                    $this->lastActionFilters($q);
                })
                    ->when($this->match_id, function ($q) {
                        // Apply game ID filter when match_id is provided
                        $q->where('games.id', $this->match_id);
                    });
            })
            ->when(!$this->match_id, function ($q) use ($lastFetchColumn, $delay) {
                // Apply last action delay when match_id is not provided
                $q->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay));
            })
            ->select('competitions.*')
            ->limit(1000)
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
            ->get();

        $this->jobStartEndLog('started', $competitions);

        // Loop through each competition to fetch and update matches
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) break;

            $this->automationInfo(($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})");
            $this->doCompetitionRunLogging();

            $seasons = $competition->seasons()
                ->when($this->task == 'fixtures', fn($q) => $q->where('is_current', true))
                ->whereDate('start_date', '>=', '2016-01-01')
                ->where('fetched_all_single_matches', false)
                ->take($this->task == 'historical_results' ? 15 : 1)
                ->orderBy('start_date', 'desc')->get();

            $total_seasons = $seasons->count();

            $should_sleep_for_seasons = false;
            $should_update_last_action = false;
            foreach ($seasons as $season_key => $season) {
                if ($should_exit) break;

                $season_games = $season->games()->count();

                $builder = $season->games()
                    ->when($this->match_id, function ($q) {
                        // Apply game ID filter when match_id is provided
                        $q->where('games.id', $this->match_id);
                    })
                    ->leftJoin('game_last_actions', 'games.id', 'game_last_actions.game_id')
                    ->whereHas('gameSources', function ($q) {
                        $q->where('game_source_id', $this->sourceContext->getId())
                            ->where(function ($q) {
                                $q->whereNotNull('source_id')
                                    ->orWhereNotNull('source_uri');
                            });
                    });

                $builder = $this->lastActionFilters($builder->whereIn('game_score_status_id', unsettledGameScoreStatuses()));
                $unsettled_games = $builder->count();

                $start_date = Str::before($season->start_date, '-');
                $end_date = Str::before($season->end_date, '-');

                $this->automationInfo(($season_key + 1) . "/{$total_seasons}. Season #{$season->id} ({$start_date}/{$end_date}, unsettled games {$unsettled_games}/{$season_games} games)");
                if ($unsettled_games === 0) continue;

                $delay_games = 90;
                if ($this->ignore_timing) $delay_games = 0;

                $games = $builder
                    ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay_games, 'game_last_actions'))
                    ->select('games.*')
                    ->limit(1000)->orderBy('game_last_actions.' . $lastFetchColumn, 'asc')
                    ->get();

                $total_games = $games->count();
                $this->automationInfo("After applying last action delay check >= {$delay_games} mins: {$total_games} games");
                if ($total_games === 0) continue;


                // Loop through each game to fetch and update matches
                foreach ($games as $key => $game) {

                    if ($this->runTimeExceeded()) {
                        $should_exit = true;
                        break;
                    }

                    $this->automationInfo(($key + 1) . "/{$total_games}. Game: #{$game->id}, {$game->utc_date}, ({$game->homeTeam->name} vs {$game->awayTeam->name}, {$game->competition->name})");

                    while (!is_connected()) {
                        $this->automationInfo("You are offline. Retrying in 10 secs...");
                        sleep(10);
                    }

                    // Capture start time
                    $requestStartTime = microtime(true);

                    // Obtain the specific handler for fetching match based on the game source strategy
                    $matchHandler = $this->sourceContext->matchHandler();

                    // Fetch matches for the current competition
                    $data = $matchHandler->fetchMatch($game->id);

                    // Output the fetch result for logging
                    $this->automationInfo($data['message'] . "");

                    // Capture end time and calculate time taken
                    $requestEndTime = microtime(true);
                    $seconds_taken = $requestEndTime - $requestStartTime;

                    // Log time taken for this game request
                    $this->automationInfo("Time taken to process Game #{$game->id}: " . round($seconds_taken / 60, 2) . " minutes");

                    $data['seconds_taken'] = round($seconds_taken);

                    $should_sleep_for_competitions = true;
                    $should_sleep_for_seasons = true;
                    $should_update_last_action = true;

                    $this->doLogging($data);
                    $this->updateLastAction($game, $should_update_last_action, $lastFetchColumn, 'game_id');

                    // Introduce a delay to avoid rapid consecutive requests
                    sleep(4);
                }

                $this->automationInfo("");
                // Introduce a delay to avoid rapid consecutive requests
                sleep($should_sleep_for_seasons ? 10 : 0);
                $should_sleep_for_seasons = false;
            }

            $this->updateLastAction($competition, $should_update_last_action, $lastFetchColumn);

            $this->automationInfo("------------\n");

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? 10 : 0);
            $should_sleep_for_competitions = false;
        }

        $this->jobStartEndLog('END');
    }

    private function lastActionFilters($query)
    {
        // Conditionally filter games based on the task being performed
        $query = $query
            ->when($this->task == 'historical_results', fn($q) => $q->where('utc_date', '<', Carbon::now()->subDays(5)))
            ->when($this->task == 'recent_results', fn($q) => $this->applyRecentResultsFilter($q))
            ->when($this->task == 'shallow_fixtures', fn($q) => $this->applyShallowFixturesFilter($q))
            ->when($this->task == 'fixtures', fn($q) => $q->where('utc_date', '>', Carbon::now()->addDays(7)));

        return $query;
    }

    function applyRecentResultsFilter(Builder $query): Builder
    {
        return $query->where('utc_date', '>', Carbon::now()->subDays(5))
            ->where('utc_date', '<', Carbon::now()->subHours(5));
    }

    function applyShallowFixturesFilter(Builder $query): Builder
    {
        return $query->where('utc_date', '>', Carbon::now())
            ->where('utc_date', '<=', Carbon::now()->addDays(7));
    }

    private function doLogging($data = null)
    {
        $updated_matches_counts = $data['results']['saved_updated'] ?? 0;
        $fetch_success_counts = $updated_matches_counts > 0 ? 1 : 0;
        $fetch_failed_counts = 0;

        $exists = $this->loggerModel();

        if ($exists) {
            $fetch_run_counts = $exists->fetch_run_counts + 1;
            $newAverageMinutes = (($exists->average_seconds_per_run * $exists->fetch_run_counts) + $data['seconds_taken']) / $fetch_run_counts;

            $arr = [
                'fetch_run_counts' => $fetch_run_counts,
                'fetch_success_counts' => $exists->fetch_success_counts + $fetch_success_counts,
                'fetch_failed_counts' => $exists->fetch_failed_counts + $fetch_failed_counts,
                'updated_matches_counts' => $exists->updated_matches_counts + $updated_matches_counts,
                'average_seconds_per_run' => $newAverageMinutes,
            ];

            $exists->update($arr);

            if ($data && $data['status'] == 500) {
                Log::info('MatchHandlerJob failer:', $data);
                $this->logFailure(new FailedMatchLog(), $data);
            }
        }
    }

    private function loggerModel($increment_job_run_counts = false)
    {
        $task = $this->task;
        $today = Carbon::now()->format('Y-m-d');
        $record = MatchJobLog::where('task', $task)->where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

        if (!$record) {
            $arr = [
                'task' => $task,
                'date' => $today,
                'source_id' => $this->sourceContext->getId(),
                'job_run_counts' => 1,
                'competition_run_counts' => 0,
                'fetch_run_counts' => 0,
                'fetch_success_counts' => 0,
                'fetch_failed_counts' => 0,
                'average_seconds_per_run' => 0,
            ];

            $record = MatchJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
