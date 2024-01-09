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

    protected $sourceContext;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // Instantiate the context class for handling game sources
        $this->sourceContext = new GameSourceStrategy();

        // Set the initial game source strategy (can be switched dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());
    }

    /**
     * Execute the job to fetch standings for competitions.
     */
    public function handle(): void
    {
        $this->loggerModel(true);

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->when(!request()->ignore_status, fn ($q) => $q->where('status_id', activeStatusId()))
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->whereHas('seasons')
            ->where('has_standings', true)
            ->where(function ($q) {
                $q->whereNull('standings_last_fetch')
                    ->orWhere('standings_last_fetch', '<=', Carbon::now()->subHours(24 * 3));
            })
            ->limit(700)
            ->orderBy('standings_last_fetch', 'asc')->get();

        // Loop through each competition to fetch and update standings
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        foreach ($competitions as $key => $competition) {
            echo ($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})\n";
            $this->doCompetitionRunLogging();

            $seasons = $competition->seasons()
                ->whereDate('start_date', '>=', '2015-01-01')
                ->where('fetched_standings', false)
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

                $this->doLogging($data);
                // Introduce a delay to avoid rapid consecutive requests
                sleep($should_sleep_for_seasons ? 15 : 0);
                $should_sleep_for_seasons = false;
            }

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
