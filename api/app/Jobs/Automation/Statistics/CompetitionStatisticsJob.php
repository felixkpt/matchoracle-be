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

class CompetitionStatisticsJob implements ShouldQueue
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
    protected $task = 'run';
    protected $ignoreTiming;
    protected $competitionId;

    /**
     * Create a new job instance.
     *
     * @param int|null $competitionId
     */
    public function __construct($task, $job_id, $ignore_timing = false, $competition_id = null)
    {
        // Set the maximum execution time (seconds)
        $this->maxExecutionTime = 60 * 10;
        $this->startTime = time();

        // Set the jobID
        $this->jobId = $job_id ?? str()->random(6);

        // Set the task property
        if ($task) {
            $this->task = $task;
        }

        if ($ignore_timing) {
            $this->ignoreTiming = $ignore_timing;
        }

        if ($competition_id) {
            $this->competitionId = $competition_id;
            request()->merge(['competition_id' => $competition_id]);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $lastFetchColumn = 'stats_last_done';
        // Set delay in minutes, 10 days is okay for this case
        $delay = 60 * 24 * 10;
        if ($this->ignoreTiming) $delay = 0;

        // Get competitions that need stats done
        $competitions = Competition::query()
            ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
            ->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))
            ->whereHas('games')
            ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
            ->select('competitions.*')
            ->limit(700)
            ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc');

        // Process competitions to calculate action counts and log job details
        $actionCounts = 0;
        foreach ((clone $competitions)->get() as $key => $competition) {
            $seasons = $this->seasonsFilter($competition);
            $total_seasons = $seasons->count();
            $actionCounts += $total_seasons;
        }

        $competition_counts = $competitions->count();
        $competitions = $competitions->when(request()->competition_id, fn($q) => $q->where('competitions.id', request()->competition_id))->get();
        $this->jobStartEndLog('START', $competitions);
        // loggerModel competition_counts and Action Counts
        $this->loggerModel(true, $competition_counts, $actionCounts);

        // Loop through each competition
        $total = $competitions->count();
        $should_exit = false;
        foreach ($competitions as $key => $competition) {
            if ($should_exit) break;

            $this->automationinfo(($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})");

            request()->merge(['competition_id' => $competition->id]);

            $seasons = $this->seasonsFilter($competition);

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
            }

            $this->updateLastAction($competition, $should_update_last_action, $lastFetchColumn);

            // Increment Completed Competition Counts
            $this->incrementCompletedCompetitionCounts();
            $this->automationInfo("------------");
        }

        if ($this->competitionId && $competitions->count() === 0) {
            $this->updateLastAction($this->getCompetition(), true, $lastFetchColumn);
        }

        $this->jobStartEndLog('END');
    }

    private function seasonsFilter($competition)
    {
        return $competition->seasons()
            ->orderBy('start_date', 'desc')->get();
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
    private function loggerModel($increment_job_run_counts = false, $competition_counts = null, $action_counts = null)
    {
        if ($this->competitionId) return;

        $today = Carbon::now()->format('Y-m-d');
        $record = CompetitionStatisticJobLog::where('date', $today)->first();

        if (!$record) {
            if ($competition_counts <= 0) {
                abort(422, 'Competition counts is needed');
            }

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
}
