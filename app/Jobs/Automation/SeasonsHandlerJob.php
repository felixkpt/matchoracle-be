<?php

namespace App\Jobs\Automation;

use App\Models\Competition;
use App\Models\FailedSeasonLog;
use App\Models\SeasonJobLog;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class SeasonsHandlerJob implements ShouldQueue
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
     * Execute the job to fetch seasons for competitions.
     */
    public function handle(): void
    {
        $this->loggerModel(true);

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $lastFetchColumn = 'seasons_last_fetch';

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn ($q) => $q->where('status_id', activeStatusId()))
            ->whereHas('gameSources', function ($q) {
                $q->where('game_source_id', $this->sourceContext->getId());
            })
            ->where(fn ($query) => $this->lastActionDelay($query, $lastFetchColumn, 24 * 15))
            ->select('competitions.*')
            ->limit(700)->orderBy('competition_last_actions.'.$lastFetchColumn, 'asc')
            ->get();

        // Loop through each competition to fetch and update seasons
        $total = $competitions->count();
        foreach ($competitions as $key => $competition) {
            echo ($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})\n";
            $this->doCompetitionRunLogging();

            while (!is_connected()) {
                echo "You are offline. Retrying in 10 secs...\n";
                sleep(10);
            }

            // Obtain the specific handler for fetching seasons based on the game source strategy
            $seasonsHandler = $this->sourceContext->seasonsHandler();

            // Fetch seasons for the current competition
            $data = $seasonsHandler->fetchSeasons($competition->id);

            // Output the fetch result for logging
            echo $data['message'] . "\n";

            $this->updateLastAction($competition, 'from_seasons', $lastFetchColumn);

            echo "------------\n";

            $this->doLogging($data);
            // Introduce a delay to avoid rapid consecutive requests
            sleep(15);
        }
    }

    private function doLogging($data = null)
    {
        $updated_seasons_counts = $data['results']['saved_updated'] ?? 0;
        $fetch_success_counts = $updated_seasons_counts > 0 ? 1 : 0;
        $fetch_failed_counts = $data ? ($updated_seasons_counts === 0 ? 1 : 0) : 0;

        $exists = $this->loggerModel();

        if ($exists) {
            $arr = [
                'fetch_run_counts' => $exists->fetch_run_counts + 1,
                'fetch_success_counts' => $exists->fetch_success_counts + $fetch_success_counts,
                'fetch_failed_counts' => $exists->fetch_failed_counts + $fetch_failed_counts,
                'updated_seasons_counts' => $exists->updated_seasons_counts + $updated_seasons_counts,
            ];


            $exists->update($arr);

            if ($fetch_failed_counts) $this->logFailure(new FailedSeasonLog(), $data);
        }
    }

    private function loggerModel($increment_job_run_counts = false)
    {
        $today = Carbon::now()->format('Y-m-d');
        $record = SeasonJobLog::where('date', $today)->where('source_id', $this->sourceContext->getId())->first();

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

            $record = SeasonJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
