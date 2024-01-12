<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\Statistics\CompetitionsPredictionsStatisticsController;
use App\Jobs\Automation\AutomationTrait;
use App\Models\Competition;
use App\Models\CompetitionPredictionStatisticJobLog;
use App\Models\GamePredictionType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CompetitionPredictionStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, AutomationTrait;

    /**
     * The competition ID for which statistics should be generated.
     *
     * @var int|null
     */
    private $competitionId;

    /**
     * Create a new job instance.
     *
     * @param int|null $competitionId
     */
    public function __construct($competitionId = null)
    {
        $this->competitionId = $competitionId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $prediction_types = GamePredictionType::all();

        foreach ($prediction_types as $prediction_type) {
            request()->merge(['prediction_type_id' => $prediction_type->id]);

            $this->loggerModel(true);

            $competitions = Competition::query()
                ->where('status_id', activeStatusId())
                ->whereHas('games')
                ->where(function ($q) {
                    $q->whereNull('predictions_stats_last_done')
                        ->orWhere('predictions_stats_last_done', '<=', Carbon::now()->subHours(24 * 0));
                })
                ->when($this->competitionId, function ($query) {
                    $query->where('id', $this->competitionId);
                })
                ->get();


            // Loop through each competition
            $total = $competitions->count();
            foreach ($competitions as $key => $competition) {
                echo ($key + 1) . "/{$total}. Competition: #{$competition->id}, ({$competition->country->name} - {$competition->name})\n";
                $this->doCompetitionRunLogging();

                request()->merge(['competition_id' => $competition->id]);

                $seasons = $competition->seasons()
                    ->whereDate('start_date', '>=', '2020-01-01')
                    ->take(15)
                    ->orderBy('start_date', 'desc')->get();

                foreach ($seasons as $season) {

                    $start_date = Str::before($season->start_date, '-');
                    $end_date = Str::before($season->end_date, '-');
                    echo "Season #{$season->id} ({$start_date}/{$end_date}), Pred type: {$prediction_type->id}\n";

                    request()->merge(['season_id' => $season->id]);
                    $data = app(CompetitionsPredictionsStatisticsController::class)->store();

                    echo $data['message'] . "\n";
                    $this->doLogging($data);
                }

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
