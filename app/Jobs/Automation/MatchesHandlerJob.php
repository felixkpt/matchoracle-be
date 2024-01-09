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

    protected $sourceContext;
    /**
     * The task to be performed by the job.
     *
     * @var string
     */
    protected $task = 'results';
    protected $ignore_date;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $ignore_date)
    {
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

        $taskType = ($this->task == 'fixtures') ? 'upcoming' : 'past';
        $lastFetchColumn = $taskType . '_matches_last_fetch';

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->when(!request()->ignore_status, fn ($q) => $q->where('status_id', activeStatusId()))
            // ->where('id', 1457)
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->whereHas('seasons')
            ->where(function ($q) use ($lastFetchColumn, $taskType) {
                $q->whereNull($lastFetchColumn)
                    ->orWhere($lastFetchColumn, '<=', Carbon::now()->subHours(($taskType == 'upcoming' ? 24 * 7 : 24 * 3)));
            })
            ->limit(700)
            ->orderBy($lastFetchColumn, 'asc')->get();

        // Loop through each competition to fetch and update matches
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        foreach ($competitions as $key => $competition) {
            echo ($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})\n";
            $this->doCompetitionRunLogging();

            $seasons = $competition->seasons()
                ->when($this->task == 'fixtures', fn ($q) => $q->where('is_current', true))
                ->whereDate('start_date', '>=', '2015-01-01')
                // ->whereDate('start_date', '<=', '2019-01-01')
                ->where('fetched_all_matches', false)
                ->take(15)
                ->orderBy('start_date', 'desc')->get();

            $should_sleep_for_seasons = false;
            foreach ($seasons as $season) {

                $start_date = Str::before($season->start_date, '-');
                $end_date = Str::before($season->end_date, '-');

                echo "Season #{$season->id} ({$start_date}/{$end_date})\n";

                while (!is_connected()) {
                    echo "You are offline. Retrying in 10 secs...\n";
                    sleep(10);
                }

                // Obtain the specific handler for fetching matches based on the game source strategy
                $matchesHandler = $this->sourceContext->matchesHandler();

                // Fetch matches for the current competition
                $data = $matchesHandler->fetchMatches($competition->id, $season->id, $this->task == 'fixtures');

                // Output the fetch result for logging
                echo $data['message'] . "\n";

                $should_sleep_for_competitions = true;
                $should_sleep_for_seasons = true;

                $this->doLogging($data);
                // Introduce a delay to avoid rapid consecutive requests
                sleep($should_sleep_for_seasons ? 15 : 0);
                $should_sleep_for_seasons = false;
            }

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

            if ($fetch_failed_counts) $this->logFailure(new FailedMatchesLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false)
    {
        $today = Carbon::now()->format('Y-m-d');
        $record = MatchesJobLog::where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

        if (!$record) {
            $arr = [
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
