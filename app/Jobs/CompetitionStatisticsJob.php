<?php

namespace App\Jobs;

use App\Http\Controllers\Admin\Statistics\CompetitionsStatisticsController;
use App\Models\Competition;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompetitionStatisticsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $competitions = Competition::whereHas('games')->get();
        echo "Total competitions with games: {$competitions->count()}\n";

        foreach ($competitions as $competition) {
            echo "Competition: {$competition->id}\n";

            request()->merge(['competition_id' => $competition->id]);

            foreach ($competition->seasons as $season) {
                request()->merge(['season_id' => $season->id]);
                app(CompetitionsStatisticsController::class)->store();
            }

            echo "\n";
        }
    }
}
