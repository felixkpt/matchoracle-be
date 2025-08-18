<?php

namespace App\Jobs\Automation;

use App\Jobs\Automation\Traits\AutomationTrait;
use App\Models\Competition;
use App\Models\FailedMatchLog;
use Illuminate\Support\Str;
use App\Models\OddJobLog;
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

class OddHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $jobId, $ignoreTiming = false, $competitionId = null, $seasonId = null, $gameId = null)
    {

        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 20;
        $this->startTime = time();

        $this->initializeSettings();

        // Set the jobID
        $this->jobId = $jobId ?? str()->random(6);

        // Instantiate the context class for handling game sources
        $this->sourceContext = new GameSourceStrategy();

        // Set the initial game source strategy (can be switched dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy($this->jobId));


        // Set the task property
        $this->task = $task ?? 'fixtures';

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

        if ($gameId) {
            $this->gameId = $gameId;
        }
    }

    /**
     * Execute the job to fetch matches for competitions.
     */
    public function handle(): void
    {

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true, 'is_odds_request' => true]);

        $lastFetchColumn = 'odd_' . $this->task . '_last_fetch';

        // Set delay in minutes based on the task type:
        $delay = $this->getDelay();
        if ($this->ignoreTiming) {
            $delay = 0;
        }

        // Get competitions that need season data updates
        $competitions = $this->getCompetitions($lastFetchColumn, $delay);

        // Process competitions to calculate action counts and log job details
        $actionCounts = 0;
        foreach ($competitions as $key => $competition) {
            $seasons = $competition->seasons;
            foreach ($seasons as $season) {
                // ensures games have game_source_id
                $games = $this->filterGames($season);
                // filters games based on the task being performed
                $games = $this->lastActionFilters($games);
                $total_games = $games->count();
                $actionCounts += $total_games;
            }
        }

        $competition_counts = $competitions->count();
        $this->logAndBroadcastJobLifecycle('START', $competitions);
        // loggerModel competition_counts and Action Counts
        $this->loggerModel(true, $competition_counts, $actionCounts);

        // Loop through each competition to fetch and update matches
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) {
                break;
            }

            $this->automationInfo(($key + 1) . "/{$total}. Competition: #{$competition->id}, Season: #{$this->seasonId}, ({$competition->country->name} - {$competition->name})");

            $seasons = $competition->seasons;

            [$should_sleep_for_competitions, $should_exit, $has_errors] = $this->workOnSeasons($seasons, $lastFetchColumn, $competition);

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

    private function getDelay(): int
    {
        switch ($this->task) {
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

    private function seasonsFilter($competitionQuery)
    {
        return $competitionQuery
            ->when($this->seasonId, fn($q) => $q->where('id', $this->seasonId))
            ->when($this->task == 'fixtures', fn($q) => $q->where('is_current', true))
            ->where('fetched_all_single_matches_odds', false)
            ->orderBy('start_date', 'desc');
    }

    private function getCompetitions($lastFetchColumn, $delay)
    {
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
            ->whereHas('games', function ($q) {

                // Check when game_id is not provided
                $q->when(!$this->gameId, function ($q) {
                    // Exclude action filters when game_id is not provided
                    $q->doesntHave('odds');
                    $this->lastActionFilters($q);
                });

                // Check when game_id is provided
                $q->when($this->gameId, function ($q) {
                    // Apply game ID filter when game_id is provided
                    $q->where('games.id', $this->gameId);
                });
            })
            ->when(!$this->gameId, function ($q) use ($lastFetchColumn, $delay) {
                // Apply last action delay when game_id is not provided
                $q->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay));
            })
            ->select('competitions.*')
            ->limit(1000)
            ->with(['seasons' => fn($q) => $this->seasonsFilter($q)])
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
            ->get();

        return $competitions;
    }

    private function filterGames($season)
    {

        return $season->games()
            ->when($this->gameId, function ($q) {
                // Apply game ID filter when game_id is provided
                $q->where('games.id', $this->gameId);
            })
            ->leftJoin('game_last_actions', 'games.id', 'game_last_actions.game_id')
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId())
                    ->where(function ($q) {
                        $q->whereNotNull('source_id')
                            ->orWhereNotNull('source_uri');
                    });
            })
            ->orderBy('utc_date', $this->task == 'historical_results' ? 'desc' : 'asc');
    }

    private function applyRecentResultsFilter(Builder $query): Builder
    {
        return $query->where('utc_date', '>', Carbon::now()->subDays(5))
            ->where('utc_date', '<', Carbon::now()->subHours(5));
    }

    function applyShallowFixturesFilter(Builder $query): Builder
    {
        return $query->where('utc_date', '>', Carbon::now())
            ->where('utc_date', '<=', Carbon::now()->addDays(7));
    }

    function applyFixturesFilter(Builder $query): Builder
    {
        return $query->where('utc_date', '>', Carbon::now()->addDays(7))->where('utc_date', '<=', Carbon::now()->addDays(90));
    }

    private function workOnSeasons($seasons, $lastFetchColumn, $competition)
    {
        $limit = $this->task == 'historical_results' ? 15 : 1;

        $total_seasons = $limit == 1 ? 1 : $seasons->count();

        $should_sleep_for_competitions = false;
        $should_sleep_for_seasons = false;
        $should_exit = false;
        $has_errors = false;

        foreach ($seasons as $season_key => $season) {
            if ($should_exit) {
                break;
            }

            if ($season_key >= $limit) {
                break;
            }

            // ensures games have game_source_id
            $games = $this->filterGames($season);

            $target_games = $games->count();

            // filters games based on the task being performed
            $games = $this->lastActionFilters($games->doesntHave('odds'));

            $start_date = Str::before($season->start_date, '-');
            $end_date = Str::before($season->end_date, '-');
            $unsettled_games = $games->count();


            $this->automationInfo("***" . ($season_key + 1) . "/{$total_seasons}. Season #{$season->id} ({$start_date}/{$end_date}, games without odds {$unsettled_games}/{$target_games} games)");
            if ($unsettled_games === 0) continue;

            $delay_games = 90;
            if ($this->ignoreTiming) $delay_games = 0;

            $games = $games
                ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay_games, 'game_last_actions'))
                ->select('games.*')
                ->limit(1000)->orderBy('game_last_actions.' . $lastFetchColumn, 'asc')
                ->get();

            $total_games = $games->count();

            $this->automationInfo("***After applying last action delay check >= {$delay_games} mins: {$total_games} games");
            if ($total_games === 0) {
                continue;
            }

            // Work on games
            [$should_sleep_for_competitions, $should_sleep_for_seasons, $should_exit, $has_errors] = $this->workOnGames($games, $total_games, $lastFetchColumn, $competition, $season);

            $should_update_last_action = !$has_errors;
            $this->updateCompetitionLastAction($competition, $should_update_last_action, $lastFetchColumn, $season->id);

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_seasons ? $this->getRequestDelaySeasons() : 0);
            $should_sleep_for_seasons = false;
        }

        return [$should_sleep_for_competitions, $should_exit, $has_errors];
    }

    private function lastActionFilters($query)
    {
        // Conditionally filter games based on the task being performed
        $query = $query
            ->when($this->task == 'historical_results', fn($q) => $q->where('utc_date', '<', Carbon::now()->subDays(5)))
            ->when($this->task == 'recent_results', fn($q) => $this->applyRecentResultsFilter($q))
            ->when($this->task == 'shallow_fixtures', fn($q) => $this->applyShallowFixturesFilter($q))
            ->when($this->task == 'fixtures', fn($q) => $this->applyFixturesFilter($q));

        return $query;
    }

    private function workOnGames($games, $total_games, $lastFetchColumn, $competition, $season)
    {

        $gamesProcessed = 0;
        $totalTimeTaken = 0;  // Tracks the cumulative time taken for processed games
        $should_sleep_for_competitions = false;
        $should_sleep_for_seasons = false;
        $should_sleep_for_games = false;
        $should_exit = false;
        $has_errors = false;
        $gamesCount = $games->count();

        // Obtain the specific handler for fetching match based on the game source strategy
        $matchHandler = $this->sourceContext->matchHandler();

        foreach ($games as $game_key => $game) {
            if ($this->runTimeExceeded()) {
                $should_exit = true;
                break;
            }

            $this->automationInfo("***" . ($game_key + 1) . "/{$total_games}. Game #{$game->id}, {$game->utc_date}, ({$game->homeTeam->name} vs {$game->awayTeam->name}, {$game->competition->name})");

            while (!is_connected()) {
                $this->automationInfo("***You are offline. Retrying in 10 secs...");
                sleep(10);
            }

            // Capture start time
            $requestStartTime = microtime(true);

            $data = $matchHandler->fetchMatch($game->id);

            // Output the fetch result for logging
            $this->automationInfo("***" . $data['message'] . "");

            // Calculate time taken for this request
            $seconds_taken = microtime(true) - $requestStartTime;
            $totalTimeTaken += $seconds_taken;  // Update total time taken for average calculation
            $gamesProcessed++;

            // Re-estimate average time per game based on cumulative time taken so far
            $estimatedTimePerGame = $totalTimeTaken / $gamesProcessed;

            // Calculate remaining games and estimated remaining time
            $remainingGames = $total_games - $gamesProcessed;
            $estimatedRemainingTime = round($remainingGames * $estimatedTimePerGame / 60);

            // Calculate total elapsed time and remaining job time
            $elapsedTime = time() - $this->startTime;  // Total time passed since the start of the job
            $remainingJobTime = round(($this->maxExecutionTime - $elapsedTime) / 60);  // Remaining time for the job in seconds

            // Log timing estimation
            $this->automationInfo(
                "***Working on Game #{$game->id} took " . $this->timeTaken($seconds_taken) . " (~{$estimatedRemainingTime} mins for remaining games, job will terminate in {$remainingJobTime} mins)."
            );

            $data['seconds_taken'] = $seconds_taken;

            if ($gamesCount > 1) {
                $should_sleep_for_competitions = true;
                $should_sleep_for_seasons = true;
                $should_sleep_for_games = true;
            }

            $should_update_last_action = true;

            if ($data['status'] === 422) {
                $should_sleep_for_games = false;
            }

            if ($data['status'] === 504) {
                $should_exit = true;
                $has_errors = true;
            }

            $this->doLogging($data);
            $this->updateGameLastAction($game, $should_update_last_action, $lastFetchColumn);

            // update last action after 15, 30, 50, 100 games the process takes time and logging can be skipped by process termination
            if ($game_key === 15 - 1 || $game_key === 30 - 1 || $game_key === 50 - 1 || $game_key === 100 - 1) {
                $this->updateCompetitionLastAction($competition, $should_update_last_action, $lastFetchColumn, $season->id);
            }

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_games ? $this->getRequestDelayGames() : 0);
            $should_sleep_for_games = false;
        }

        return [$should_sleep_for_competitions, $should_sleep_for_seasons, $should_exit, $has_errors];
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

            if ($data && $data['status'] == 500) {
                Log::info('MatchHandlerJob failer:', $data);
                $this->logFailure(new FailedMatchLog(), $data);
            }
        }
    }

    private function loggerModel($increment_job_run_counts = false, $competition_counts = null, $action_counts = null)
    {
        if ($this->competitionId) {
            return;
        }

        $task = $this->task;
        $today = Carbon::now()->format('Y-m-d');
        $record = OddJobLog::where('task', $task)->where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

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

            $record = OddJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
