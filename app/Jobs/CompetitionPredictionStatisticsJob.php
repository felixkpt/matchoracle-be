<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\Statistics\CompetitionsPredictionsStatisticsController;
use App\Models\Competition;
use App\Models\GamePredictionType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompetitionPredictionStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $competitions = Competition::whereHas('games')
            ->when($this->competitionId, function ($query) {
                $query->where('id', $this->competitionId);
            })
            ->get();
        echo "Total competitions with games: {$competitions->count()}\n";

        $prediction_types = GamePredictionType::all();

        foreach ($prediction_types as $prediction_type) {
            request()->merge(['prediction_type_id' => $prediction_type->id]);

            foreach ($competitions as $competition) {

                request()->merge(['competition_id' => $competition->id]);

                foreach ($competition->seasons as $season) {
                    echo "Pred type: {$prediction_type->id}, Competition: {$competition->id}, Season: {$season->id}\n";

                    request()->merge(['season_id' => $season->id]);
                    app(CompetitionsPredictionsStatisticsController::class)->store();
                    // die;
                }

                echo "\n";
            }
        }
    }
}
