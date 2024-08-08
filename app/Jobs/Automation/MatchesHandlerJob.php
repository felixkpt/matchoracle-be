<?php

namespace App\Jobs\Automation;

use App\Models\Competition;
use App\Models\FailedMatchesLog;
use App\Models\MatchesJobLog;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class MatchesHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * The task to be performed by the job.
     *
     * @var string
     */
    protected $task = 'recent_results';
    protected $ignore_timing;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $competition_id, $ignore_timing)
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

        if ($competition_id) {
            request()->merge(['competition_id' => $competition_id]);
        }

        if ($ignore_timing) {
            $this->ignore_timing = $ignore_timing;
        }
    }

    /**
     * Execute the job to fetch matches for competitions.
     */
    public function handle(): void
    {

        $this->loggerModel(true);

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true, 'shallow_fetch' => $this->task == 'shallow_fixtures']);

        $lastFetchColumn = 'matches_' . $this->task . '_last_fetch';

        // Set delay in minutes based on the task type:
        // Default case for historical_results
        $delay = 60 * 24 * 3;
        if ($this->task == 'shallow_fixtures') {
            $delay = 60 * 24 * 2;
        } elseif ($this->task == 'fixtures') {
            $delay = 60 * 24 * 3;
        } elseif ($this->task == 'recent_results') {
            $delay = 60 * 24 * 2;
        }

        // Get competitions that need matches data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn ($q) => $q->where('status_id', activeStatusId()))
            ->when(request()->competition_id, fn ($q) => $q->where('competitions.id', request()->competition_id))
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->whereHas('seasons')
            ->when($this->task == 'recent_results', fn ($q) => $q->whereHas('games', fn ($q) => $q->where('utc_date', '>', Carbon::now()->subDays(5))->where('utc_date', '<', Carbon::now()->subHours(5))))
            // ->where(fn ($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(700)
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
            ->get();

        // Loop through each competition to fetch and update matches
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        foreach ($competitions as $key => $competition) {
            echo ($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})\n";
            $this->doCompetitionRunLogging();

            $seasons = $competition->seasons()
                ->when(Str::endsWith($this->task, 'fixtures'), fn ($q) => $q->where('is_current', true))
                ->whereDate('start_date', '>=', '2015-01-01')
                ->where('fetched_all_matches', false)
                ->take($this->task == 'historical_results' ? 15 : 1)
                ->orderBy('updated_at', 'asc')->get();
            $total_seasons = $seasons->count();

            $should_sleep_for_seasons = false;
            $should_update_last_action = false;
            foreach ($seasons as $season_key => $season) {

                if ($this->runTimeExceeded()) exit;

                $start_date = Str::before($season->start_date, '-');
                $end_date = Str::before($season->end_date, '-');

                echo ($season_key + 1) . "/{$total_seasons}. Season #{$season->id} ({$start_date}/{$end_date})\n";

                while (!is_connected()) {
                    echo "You are offline. Retrying in 10 secs...\n";
                    sleep(10);
                }

                // Obtain the specific handler for fetching matches based on the game source strategy
                $matchesHandler = $this->sourceContext->matchesHandler();

                // Fetch matches for the current competition
                $data = $matchesHandler->fetchMatches($competition->id, $season->id, Str::endsWith($this->task, 'fixtures'));

                // Output the fetch result for logging
                echo $data['message'] . "\n";

                $should_sleep_for_competitions = true;
                $should_sleep_for_seasons = true;
                $should_update_last_action = true;

                $this->doLogging($data);
                // Introduce a delay to avoid rapid consecutive requests
                sleep($should_sleep_for_seasons ? 15 : 0);
                $should_sleep_for_seasons = false;
            }

            $this->updateLastAction($competition, $should_update_last_action, $lastFetchColumn);

            $this->determineCompetitionGamesPerSeason($competition, $seasons);

            echo "------------\n";

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? 15 : 0);
            $should_sleep_for_competitions = false;
        }
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

            if ($fetch_failed_counts || ($data && isset($data['status']) && $data['status'] == 500)) $this->logFailure(new FailedMatchesLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false)
    {
        $task = $this->task;
        $today = Carbon::now()->format('Y-m-d');
        $record = MatchesJobLog::where('task', $task)->where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

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
                'updated_matches_counts' => 0,
            ];

            $record = MatchesJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }

    private function determineCompetitionGamesPerSeason($competition, $seasons)
    {
        if ($this->task == 'fixtures') return false;

        $teams_counts = $competition->teams()->count();
        $expected_games_per_season = intval(2 * ($teams_counts - 1) * ($teams_counts / 2));

        if ($expected_games_per_season === 0) return false;

        echo "Teams counts: {$teams_counts}, expected games per season: {$expected_games_per_season}\n";

        $season_matches_arr = [];
        foreach ($seasons as $season) {
            $start_date = Str::before($season->start_date, '-');
            $end_date = Str::before($season->end_date, '-');

            $season_games = $season->games()->count();
            $season_matches_arr[] = $season_games;

            echo "Season #{$season->id} ({$start_date}/{$end_date}, {$season_games} games)\n";
        }

        // season average matches is count of most repeated match counts
        rsort($season_matches_arr);
        $season_matches_arr = array_filter($season_matches_arr, fn ($val) => $val >= $expected_games_per_season);

        echo "Counts after filtering >= expected_games_per_season: " . count($season_matches_arr) . "\n";

        if (count($season_matches_arr) >= 3) {

            // Get the first three most repeated counts
            $most_repeated_counts = array_slice($season_matches_arr, 0, 3);

            $games_per_season = intval(array_sum($most_repeated_counts) / 3);
            // Check if the first two counts are the same
            if (count(array_count_values($most_repeated_counts)) == 1 || $games_per_season == $expected_games_per_season) {

                if ($games_per_season > 0 && $competition->games_per_season != $games_per_season) {
                    echo "Games per season: {$games_per_season} games\n";

                    $competition->games_per_season = $games_per_season;
                    $competition->save();
                }
            } else {
                echo "Games per season: could not be determined\n";
            }
        }
    }
}
