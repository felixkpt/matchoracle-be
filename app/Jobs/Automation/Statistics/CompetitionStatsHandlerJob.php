<?php

namespace App\Jobs\Automation\Statistics;

use App\Jobs\Automation\Traits\AutomationTrait;
use App\Models\Competition;
use App\Models\CompetitionStatisticJobLog;
use App\Models\CompetitionStatistics;
use App\Repositories\Statistics\CompetitionStatisticsRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CompetitionStatsHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * Create a new job instance.
     *
     * @param int|null $competitionId
     */
    public function __construct($task, $jobId, $ignoreTiming = false, $competitionId = null, $seasonId = null)
    {
        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 10;
        $this->startTime = time();

        // Set the jobID
        $this->jobId = $jobId ?? str()->random(6);

        // Set the task property
        $this->task = $task ?? 'run';

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
     * Execute the job.
     */
    public function handle(): void
    {

        $this->lastFetchColumn = 'stats_last_done';
        // Set delay in minutes, 10 days is okay for this case
        $delay = 60 * 24 * 10;
        if ($this->ignoreTiming) {
            $delay = 0;
        }

        // Get competitions that need stats done
        $competitions = $this->getCompetitions($delay);

        // Process competitions to calculate action counts and log job details
        $actionCounts = 0;
        foreach ($competitions as $key => $competition) {
            $seasons = $competition->seasons;
            $total_seasons = $seasons->count();
            $actionCounts += $total_seasons;
        }

        $competition_counts = $competitions->count();

        $this->logAndBroadcastJobLifecycle('START', $competitions);
        // loggerModel competition_counts and Action Counts
        $this->loggerModel(true, $competition_counts, $actionCounts);

        // Loop through each competition
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) {
                break;
            }

            $this->automationinfo(($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})");

            request()->merge(['competition_id' => $competition->id]);

            $seasons = $competition->seasons;

            $should_update_last_action = true;
            foreach ($seasons as $season) {

                if ($this->runTimeExceeded()) {
                    $should_exit = true;
                    break;
                }

                $start_date = Str::before($season->start_date, '-');
                $end_date = Str::before($season->end_date, '-');
                $this->automationinfo("Season #{$season->id} ({$start_date}/{$end_date})");

                request()->merge(['season_id' => $season->id]);
                $data = (new CompetitionStatisticsRepository(new CompetitionStatistics()))->store();

                $this->automationinfo($data['message'] . "");
                $this->doLogging($data);

                $this->updateCompetitionLastAction($competition, $should_update_last_action, $this->lastFetchColumn, $season->id);
            }

            // Increment Completed Competition Counts
            $this->incrementCompletedCompetitionCounts();
            $this->automationInfo("------------");
        }

        if ($this->competitionId && $competitions->count() === 0) {
            $this->updateCompetitionLastAction($this->getCompetition(), true, $this->lastFetchColumn, $this->seasonId);
        }

        $this->logAndBroadcastJobLifecycle('END');
    }

    private function doLogging($data = null)
    {

        $games_run_counts = $data['results']['updated'] ?? 0;
        $exists = $this->loggerModel();

        if ($exists) {
            $arr = [
                'seasons_run_counts' => $exists->seasons_run_counts + 1,
                'games_run_counts' => $exists->games_run_counts + $games_run_counts,
            ];

            $exists->update($arr);
        }
    }
    private function loggerModel($increment_job_run_counts = false, $competition_counts = 1, $action_counts = 1)
    {
        if ($this->competitionId) {
            return;
        }

        $today = Carbon::now()->format('Y-m-d');
        $record = CompetitionStatisticJobLog::where('date', $today)->first();

        if (!$record) {

            $arr = [
                'date' => $today,
                'job_run_counts' => 1,
                'competition_counts' => $competition_counts,
                'action_counts' => $action_counts,
            ];

            $record = CompetitionStatisticJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }

    private function seasonsFilter($competitionQuery)
    {
        return $competitionQuery
            ->when($this->seasonId, fn($q) => $q->where('id', $this->seasonId))
            // ->where('fetched_standings', false)
            ->orderBy('start_date', 'desc');
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
            ->where(fn($query) => $this->lastActionDelay($query, $this->lastFetchColumn, $delay))
            ->where('competitions.has_standings', true)
            ->select('competitions.*')
            ->limit(1000)
            ->with(['seasons' => fn($q) => $this->seasonsFilter($q)])
            ->whereHas('games')
            ->orderBy('competition_last_actions.' . $this->lastFetchColumn, 'asc')
            ->get();
    }
}
