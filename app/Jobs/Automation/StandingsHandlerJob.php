<?php

namespace App\Jobs\Automation;

use App\Models\Competition;
use App\Models\FailedStandingLog;
use App\Models\StandingJobLog;
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

class StandingsHandlerJob implements ShouldQueue
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
     * Execute the job to fetch standings for competitions.
     */
    public function handle(): void
    {

        $this->loggerModel(true);

        // Set the request parameter to indicate no direct response is expected
        request()->merge(['without_response' => true]);

        $lastFetchColumn = 'standings_' . $this->task . '_last_fetch';

        $delay = 60 * 24 * 15;
        if ($this->ignore_timing) $delay = 0;

        // Fetch competitions that need season data updates
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))
            ->when(request()->competition_id, fn($q) => $q->where('competitions.id', request()->competition_id))
            ->whereHas('gameSources', function ($query) {
                $query->where('game_source_id', $this->sourceContext->getId());
            })
            ->whereHas('seasons')
            ->when($this->task == 'recent_results', fn($q) => $this->applyRecentResultsFilter($q))
            ->where('has_standings', true)
            ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(700)
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
            ->get();

        $this->jobStartEndLog('START', $competitions);

        // Loop through each competition to fetch and update standings
        $should_sleep_for_competitions = false;
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) break;

            if ($this->runTimeExceeded()) {
                $should_exit = true;
                break;
            }

            $this->automationInfo($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})";
            $this->doCompetitionRunLogging();

            $seasons = $competition->seasons()
                ->whereDate('start_date', '>=', $this->historyStartDate)
                ->where('fetched_standings', false)
                ->take(15)
                ->orderBy('start_date', 'desc')->get();
            $total_seasons = $seasons->count();

            $should_sleep_for_seasons = false;
            $should_update_last_action = false;
            foreach ($seasons as $season_key => $season) {

                $start_date = Str::before($season->start_date, '-');
                $end_date = Str::before($season->end_date, '-');

                $this->automationInfo(($season_key + 1) . "/{$total_seasons}. Season #{$season->id} ({$start_date}/{$end_date})");

                while (!is_connected()) {
                    $this->automationInfo("You are offline. Retrying in 10 secs...");
                    sleep(10);
                }

                // Obtain the specific handler for fetching standings based on the game source strategy
                $standingsHandler = $this->sourceContext->standingsHandler();

                // Fetch standings for the current competition
                $data = $standingsHandler->fetchStandings($competition->id, $season->id);

                // recheck if compe has_standings
                if (!Competition::find($competition->id)->has_standings) break;

                // Output the fetch result for logging
                $this->automationInfo($data['message'] . "");

                $should_sleep_for_competitions = true;
                $should_sleep_for_seasons = true;
                $should_update_last_action = true;

                $this->doLogging($data);

                if ($data['status'] === 504) {
                    $should_exit = true;
                    $should_update_last_action = false;
                    break;
                }

                // Introduce a delay to avoid rapid consecutive requests
                sleep($should_sleep_for_seasons ? 15 : 0);
                $should_sleep_for_seasons = false;
            }

            $this->updateLastAction($competition, $should_update_last_action, $lastFetchColumn);

            $this->automationInfo("------------");

            // Introduce a delay to avoid rapid consecutive requests
            sleep($should_sleep_for_competitions ? 15 : 0);
            $should_sleep_for_competitions = false;
        }

        $this->jobStartEndLog('END');
    }

    function applyRecentResultsFilter(Builder $query): Builder
    {
        return $query->whereHas('games', function ($subQuery) {
            $subQuery->where('utc_date', '>=', Carbon::now()->subDays(5))
                ->where('utc_date', '<', Carbon::now())
                ->where('game_score_status_id', gameScoresStatus('scheduled'));
        });
    }

    private function doLogging($data = null)
    {
        $created_counts = $data['results']['created_counts'] ?? 0;
        $updated_counts = $data['results']['updated_counts'] ?? 0;
        $failed_counts = $data['results']['failed_counts'] ?? 0;

        $exists = $this->loggerModel();

        if ($exists) {
            $action_run_counts = $exists->action_run_counts + 1;
            $newAverageMinutes = (($exists->average_seconds_per_action_run * $exists->action_run_counts) + $data['seconds_taken']) / $action_run_counts;

            $arr = [
                'action_run_counts' => $action_run_counts,
                'average_seconds_per_action_run' => $newAverageMinutes,
                'created_counts' => $exists->created_counts + $created_counts,
                'updated_counts' => $exists->updated_counts + $updated_counts,
                'failed_counts' => $exists->failed_counts + $failed_counts,
            ];

            $exists->update($arr);

            if ($failed_counts || ($data && $data['status'] == 500)) $this->logFailure(new FailedStandingLog(), $data);
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
            ];

            $record = StandingJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
