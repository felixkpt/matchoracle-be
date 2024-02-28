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
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * The task to be performed by the job.
     *
     * @var string
     */
    protected $task = 'recent_results';
    protected $ignore_date;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $ignore_date)
    {
        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 10;
        $this->startTime = time();

        // Instantiate the context class for handling game sources
        $this->sourceContext = new GameSourceStrategy();

        // Set the initial game source strategy (can be switched dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
        // Set the task property
        if ($task) {
            $this->task = $task;
        }
        if ($ignore_date) {
            $this->ignore_date = $ignore_date;
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

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn ($q) => $q->where('status_id', activeStatusId()))
            // ->where('id', 1622)
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->whereHas('games', function ($q) {
                $q->where('results_status', '<', 2);
                $this->lastActionFilters($q);
            })
            ->where(fn ($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(1000)->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
            ->get();

        // Loop through each competition to fetch and update matches
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        foreach ($competitions as $key => $competition) {
            echo ($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})\n";
            $this->doCompetitionRunLogging();

            $seasons = $competition->seasons()
                ->when($this->task == 'fixtures', fn ($q) => $q->where('is_current', true))
                ->whereDate('start_date', '>=', '2020-01-01')
                ->where('fetched_all_single_matches', false)
                ->take($this->task == 'historical_results' ? 15 : 1)
                ->orderBy('start_date', 'desc')->get();
            $total_seasons = $seasons->count();

            $should_sleep_for_seasons = false;
            $should_update_last_action = false;
            foreach ($seasons as $season_key => $season) {
                $season_games = $season->games()->count();

                $builder = $season->games()
                    ->leftJoin('game_last_actions', 'games.id', 'game_last_actions.game_id')
                    ->whereHas('gameSources', function ($q) {
                        $q->where('game_source_id', $this->sourceContext->getId())
                            ->where(function ($q) {
                                $q->whereNotNull('source_id')
                                    ->orWhereNotNull('source_uri');
                            });
                    });

                $builder = $this->lastActionFilters($builder);

                $start_date = Str::before($season->start_date, '-');
                $end_date = Str::before($season->end_date, '-');

                echo ($season_key + 1) . "/{$total_seasons}. Season #{$season->id} ({$start_date}/{$end_date}, untouched games {$builder->count()}/{$season_games} games)\n";

                $delay_games = 0;
                $games = $builder
                    ->where(fn ($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay, 'game_last_actions'))
                    ->select('games.*')
                    ->limit(1000)->orderBy('game_last_actions.' . $lastFetchColumn, 'asc')
                    ->get();

                $total_games = $games->count();
                echo "After applying last fetch check >= {$delay_games} mins: {$total_games} games\n";
                if ($total_games === 0) continue;

                // Loop through each game to fetch and update matches
                foreach ($games as $key => $game) {

                    if ($this->runTimeExceeded()) exit;

                    echo ($key + 1) . "/{$total_games}. Game: #{$game->id}, {$game->utc_date}, ({$game->homeTeam->name} vs {$game->awayTeam->name}, {$game->competition->name})\n";

                    while (!is_connected()) {
                        echo "You are offline. Retrying in 10 secs...\n";
                        sleep(10);
                    }

                    // Obtain the specific handler for fetching match based on the game source strategy
                    $matchHandler = $this->sourceContext->matchHandler();

                    // Fetch matches for the current competition
                    $data = $matchHandler->fetchMatch($game->id);

                    // Output the fetch result for logging
                    echo $data['message'] . "\n";

                    if (Str::startsWith($data['message'], 'Last fetch is')) continue;
                    if (Str::startsWith($data['message'], 'No source/details uri')) continue;

                    $should_sleep_for_competitions = true;
                    $should_sleep_for_seasons = true;
                    $$should_update_last_action = true;

                    $this->doLogging($data);
                    $this->updateLastAction($game, $should_update_last_action, $lastFetchColumn, 'game_id');

                    // Introduce a delay to avoid rapid consecutive requests
                    sleep(4);
                }

                echo "\n";
                // Introduce a delay to avoid rapid consecutive requests
                sleep($should_sleep_for_seasons ? 10 : 0);
                $should_sleep_for_seasons = false;
            }

            $this->updateLastAction($competition, $should_update_last_action, $lastFetchColumn);

            echo "------------\n\n";

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? 10 : 0);
            $should_sleep_for_competitions = false;
        }
    }

    private function lastActionFilters($query)
    {
        // Conditionally filter games based on the task being performed
        $query = $query->when($this->task == 'historical_results', function ($q) {
            $q->where('utc_date', '<', Carbon::now()->subDays(5));
        })
            ->when($this->task == 'recent_results', function ($q) {
                $q->where('utc_date', '>=', Carbon::now()->subDays(5))
                    ->where('utc_date', '<=', Carbon::now()->subHours(5));
            })
            ->when($this->task == 'shallow_fixtures', fn ($q) => $q
                ->where('utc_date', '>', Carbon::now())
                ->where('utc_date', '<=', Carbon::now()->addDays(7)))
            ->when($this->task == 'fixtures', fn ($q) => $q
                ->where('utc_date', '>', Carbon::now()->addDays(7)));

        return $query;
    }

    private function doLogging($data = null)
    {
        $updated_matches_counts = $data['results']['saved_updated'] ?? 0;
        $fetch_success_counts = $updated_matches_counts > 0 ? 1 : 0;
        $fetch_failed_counts = $data ? ($updated_matches_counts === 0 ? 1 : 0) : 0;

        $exists = $this->loggerModel();

        if ($exists) {
            $arr = [
                'fetch_run_counts' => $exists->fetch_run_counts + 1,
                'fetch_success_counts' => $exists->fetch_success_counts + $fetch_success_counts,
                'fetch_failed_counts' => $exists->fetch_failed_counts + $fetch_failed_counts,
                'updated_matches_counts' => $exists->updated_matches_counts + $updated_matches_counts,
            ];

            $exists->update($arr);

            if ($fetch_failed_counts) $this->logFailure(new FailedMatchLog(), $data);
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
                'updated_standings_counts' => 0,
            ];

            $record = MatchJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
