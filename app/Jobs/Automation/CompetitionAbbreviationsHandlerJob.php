<?php

namespace App\Jobs\Automation;

use App\Jobs\Automation\Traits\AutomationTrait;
use App\Models\Competition;
use App\Services\GameSources\Forebet\ForebetStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompetitionAbbreviationsHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * Create a new job instance.
     */
    public function __construct($task, $jobId, $ignoreTiming = false, $competitionId = null, $seasonId = null)
    {
        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 30;
        $this->startTime = time();

        $this->initializeSettings();

        // Instantiate the context class for handling game sources
        $this->sourceContext = new GameSourceStrategy();

        // Set the initial game source strategy (can be switched dynamically)
        $this->sourceContext->setGameSourceStrategy(new ForebetStrategy());

        // Set the jobID
        $this->jobId = $jobId ?? str()->random(6);

        // Set the task property
        $this->task = $task ?? 'fetch';

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
    }

    /**
     * Execute the job to fetch seasons for competitions.
     */
    public function handle(): void
    {
        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $this->lastFetchColumn = 'abbreviations_last_fetch';

        $delay = 60 * 24 * 30;
        if ($this->ignoreTiming) {
            $delay = 0;
        }

        // Fetch competitions that need season data updates
        $competitions = $this->getCompetitions($delay);

        // Process competitions to calculate action counts and log job details
        $this->logAndBroadcastJobLifecycle('START', $competitions);

        // Loop through each competition to fetch and update seasons
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) {
                break;
            }

            if ($this->runTimeExceeded()) {
                $should_exit = true;
                break;
            }

            $this->automationInfo(($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})");

            while (!is_connected()) {
                $this->automationInfo("You are offline. Retrying in 10 secs...");
                sleep(10);
            }

            // Capture start time
            $requestStartTime = microtime(true);

            // Obtain the specific handler for fetching abbreviation based on the game source strategy
            $abbreviationsHandler = $this->sourceContext->competitionAbbreviationsHandler();

            // Fetch abbreviation for the current competition
            $data = $abbreviationsHandler->fetch($competition->id);

            // Output the fetch result for logging
            $this->automationInfo("***" . $data['message'] . "");

            // Capture end time and calculate time taken
            $requestEndTime = microtime(true);
            $seconds_taken = intval($requestEndTime - $requestStartTime);

            // Log time taken for this game request
            $this->automationInfo("***Time taken working on Compe #{$competition->id}: " . $this->timeTaken($seconds_taken));

            $data['seconds_taken'] = $seconds_taken;

            $should_sleep_for_competitions = true;

            if ($data['status'] === 504) {
                $should_exit = true;
            }

            $this->automationInfo("------------");

            $this->updateCompetitionLastAction($competition, true, $this->lastFetchColumn, $this->seasonId);

            $should_sleep_for_competitions = true;

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? $this->getRequestDelayCompetitions() : 0);
            $should_sleep_for_competitions = false;
        }

        if ($this->competitionId && $competitions->count() === 0) {
            $this->updateCompetitionLastAction($this->getCompetition(), true, $this->lastFetchColumn, $this->seasonId);
        }

        $this->logAndBroadcastJobLifecycle('END');
    }

    private function getCompetitions($delay)
    {
        return Competition::query()
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
            ->whereDoesntHave('abbreviation')
            ->where(fn($query) => $this->lastActionDelay($query, $this->lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(1000)
            ->orderBy('competition_last_actions.' . $this->lastFetchColumn, 'asc')
            ->get();
    }
}
