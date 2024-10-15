<?php

namespace App\Jobs\Statistics;

use App\Jobs\Automation\AutomationTrait;
use App\Models\Competition;
use App\Models\CompetitionPredictionStatistic;
use App\Models\CompetitionPredictionStatisticJobLog;
use App\Models\GamePredictionType;
use App\Repositories\Statistics\CompetitionPredictionStatisticsRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompetitionPredictionStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * The competition ID for which statistics should be generated.
     *
     * @var int|null
     */
    protected $task = 'stats';
    private $ignore_timing = false;
    private $competition_id;

    /**
     * Create a new job instance.
     *
     * @param int|null $competitionId
     */
    public function __construct($task, $ignore_timing = false, $competition_id = null)
    {

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
     * Execute the job.
     */
    public function handle(): void
    {
        $this->jobStartedLog();

        $prediction_types = GamePredictionType::all();

        request()->merge(['prediction_mode_id' => 1]);

        foreach ($prediction_types as $index => $prediction_type) {
            request()->merge(['prediction_type_id' => $prediction_type->id]);

            $this->loggerModel(true);

            $lastFetchColumn = 'predictions_stats_last_done';
            // Set delay in minutes, 10 days is okay for this case
            $delay = 60 * 24 * 10;
            if ($this->ignore_timing) $delay = 0;

            // Get competitions that need stats done
            $competitions = Competition::query()
                ->leftJoin('competition_last_actions', 'competitions.id', 'competition_last_actions.competition_id')
                ->when(!request()->ignore_status, fn($q) => $q->where('status_id', activeStatusId()))
                ->where('games_counts', '>=', 500)
                ->when($this->competition_id, function ($query) {
                    $query->where('competitions.id', $this->competition_id);
                })
                ->whereHas('games')
                ->where(fn($query) => $this->lastActionDelay($query, $lastFetchColumn, $delay))
                ->select('competitions.*')
                ->limit(1000)
                ->orderBy('competition_last_actions.' . $lastFetchColumn, 'asc')
                ->get();

            // Loop through each competition & do stats
            $total = $competitions->count();
            foreach ($competitions as $key => $competition) {
                echo sprintf(
                    "[Pred %d/%d] - %d/%d. Competition: #%s, (%s - %s)\n",
                    $index + 1,
                    count($prediction_types),
                    $key + 1,
                    $total,
                    $competition->id,
                    $competition->country->name,
                    $competition->name,
                );

                $this->doCompetitionRunLogging();

                request()->merge(['competition_id' => $competition->id]);

                $seasons = $competition->seasons()
                    ->whereDate('start_date', '>=', '2020-01-01')
                    ->take(15)
                    ->orderBy('start_date', 'desc')->get();

                $should_update_last_action = true;

                foreach ($seasons as $season) {

                    $start_date = Str::before($season->start_date, '-');
                    $end_date = Str::before($season->end_date, '-');
                    echo "Season #{$season->id} ({$start_date}/{$end_date}), Pred type: {$prediction_type->id}\n";

                    request()->merge(['season_id' => $season->id]);
                    $data = (new CompetitionPredictionStatisticsRepository(new CompetitionPredictionStatistic()))->store();


                    echo $data['message'] . "\n";
                    $this->doLogging($data);
                }

                $this->updateLastAction($competition, $should_update_last_action, $lastFetchColumn);

                echo "------------\n";
            }
        }
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

    private function loggerModel($increment_job_run_counts = false)
    {
        $today = Carbon::now()->format('Y-m-d');
        $record = CompetitionPredictionStatisticJobLog::where('prediction_type_id', request()->prediction_type_id)->where('date', $today)->first();

        if (!$record) {
            $arr = [
                'prediction_type_id' => request()->prediction_type_id,
                'date' => $today,
                'job_run_counts' => 1,
                'competition_run_counts' => 0,
                'seasons_run_counts' => 0,
                'games_run_counts' => 0,
            ];

            $record = CompetitionPredictionStatisticJobLog::create($arr);
        } elseif ($increment_job_run_counts) $record->update(['job_run_counts' => $record->job_run_counts + 1]);

        return $record;
    }
}
