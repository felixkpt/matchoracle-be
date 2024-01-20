<?php

namespace App\Jobs\Automation;

use App\Models\Competition;
use App\Models\FailedStandingLog;
use App\Models\StandingJobLog;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class StandingsHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * The task to be performed by the job.
     *
     * @var string
     */
    protected $task = 'recent_results';

    /**
     * Create a new job instance.
     */
    public function __construct($task)
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
    }

    /**
     * Execute the job to fetch standings for competitions.
     */
    public function handle(): void
    {
        $this->loggerModel(true);

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $lastFetchColumn = 'standings_' . $this->task . '_last_fetch';

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, function ($query) {
                $query->where('status_id', activeStatusId());
            })
            ->whereHas('gameSources', function ($query) {
                $query->where('game_source_id', $this->sourceContext->getId());
            })
            ->whereHas('seasons')
            ->when($this->task == 'recent_results', function ($query) {
                $query->whereHas('games', function ($subQuery) {
                    $subQuery->where('utc_date', '>=', Carbon::now()->subDays(5))
                        ->where('utc_date', '<', Carbon::now())
                        ->where('results_status', '>', 0);
                });
            })
            ->where('has_standings', true)
            ->where(fn ($query) => $this->lastActionDelay($query, $lastFetchColumn, 60 * 24 * 2))
            ->select('competitions.*')
            ->limit(700)
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
            ->get();

        // Loop through each competition to fetch and update standings
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        foreach ($competitions as $key => $competition) {

            if ($this->runTimeExceeded()) exit;
            
            echo ($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})\n";
            $this->doCompetitionRunLogging();

            $seasons = $competition->seasons()
                ->whereDate('start_date', '>=', '2015-01-01')
                ->where('fetched_standings', false)
                ->take(15)
                ->orderBy('start_date', 'desc')->get();
            $total_seasons = $seasons->count();

            $should_sleep_for_seasons = false;
            $should_update_last_action = false;
            foreach ($seasons as $season_key => $season) {

                $start_date = Str::before($season->start_date, '-');
                $end_date = Str::before($season->end_date, '-');

                echo ($season_key + 1) . "/{$total_seasons}. Season #{$season->id} ({$start_date}/{$end_date})\n";

                while (!is_connected()) {
                    echo "You are offline. Retrying in 10 secs...\n";
                    sleep(10);
                }

                // Obtain the specific handler for fetching standings based on the game source strategy
                $standingsHandler = $this->sourceContext->standingsHandler();

                // Fetch standings for the current competition
                $data = $standingsHandler->fetchStandings($competition->id, $season->id);

                // recheck if compe has_standings
                if (!Competition::find($competition->id)->has_standings) break;

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

            echo "------------\n";

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? 15 : 0);
            $should_sleep_for_competitions = false;
        }
    }

    private function doLogging($data = null)
    {
        $updated_standings_counts = $data['results']['saved_updated'] ?? 0;
        $fetch_success_counts = $updated_standings_counts > 0 ? 1 : 0;
        $fetch_failed_counts = $data ? ($updated_standings_counts === 0 ? 1 : 0) : 0;

        $exists = $this->loggerModel();

        if ($exists) {
            $arr = [
                'fetch_run_counts' => $exists->fetch_run_counts + 1,
                'fetch_success_counts' => $exists->fetch_success_counts + $fetch_success_counts,
                'fetch_failed_counts' => $exists->fetch_failed_counts + $fetch_failed_counts,
                'updated_standings_counts' => $exists->updated_standings_counts + $updated_standings_counts,
            ];

            $exists->update($arr);

            if ($fetch_failed_counts) $this->logFailure(new FailedStandingLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false)
    {
        $today = Carbon::now()->format('Y-m-d');
        $record = StandingJobLog::where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

        if (!$record) {
            $arr = [
                'date' => $today,
                'source_id' => $this->sourceContext->getId(),
                'job_run_counts' => 1,
                'competition_run_counts' => 0,
                'fetch_run_counts' => 0,
                'fetch_success_counts' => 0,
                'fetch_failed_counts' => 0,
                'updated_standings_counts' => 0,
            ];

            $record = StandingJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
