<?php

namespace App\Repositories\Statistics;

use App\Console\Commands\Statistics\CompetitionPredictionStatistics;
use App\Models\CompetitionStatistic;
use App\Models\MatchdayCompetitionStatistic;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\Game\GameRepositoryInterface;
use App\Repositories\GameComposer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CompetitionPredictionStatisticsRepository implements CompetitionPredictionStatisticsRepositoryInterface
{

    use CommonRepoActions;

    function __construct(
        protected CompetitionPredictionStatistics $model,
        protected GameRepositoryInterface $gameRepositoryInterface
    ) {
    }

    function index()
    {

        $results = $this->model->where('competition_id', request()->competition_id)
            ->when(request()->season_id, fn ($q) => $q->where('season_id', request()->season_id))
            ->first();

        return response(['results' => $results]);
    }

    public function store()
    {

        $competition_id = request()->competition_id;
        $season = Season::find(request()->season_id);
        $season_id = $season->id;

        request()->merge(['per_page' => 700, 'order_by' => 'utc_date', 'order_direction' => 'asc']);

        $games = $this->gameRepositoryInterface->index(null, true);

        $counts = 0;

        $full_time_home_wins_counts = 0;
        $full_time_draws_counts = 0;
        $full_time_away_wins_counts = 0;
        $half_time_home_wins_counts = 0;
        $half_time_draws_counts = 0;
        $half_time_away_wins_counts = 0;

        $gg_counts = 0;
        $ng_counts = 0;

        $over15_counts = 0;
        $under15_counts = 0;
        $over25_counts = 0;
        $under25_counts = 0;
        $over35_counts = 0;
        $under35_counts = 0;

        $games = $games['data'];
        $unique_dates = array_unique(array_column($games['data'], 'utc_date'));
        Log::info("DATES", $unique_dates);

        $ct = count($games);
        echo "Total games for competition #{$competition_id}: $ct\n\n";

        $matchday = 0;
        foreach ($games as $game) {
            $id = $game['id'];
            $date = $game['utc_date'];
            $score = $game['score'];
            $matchday = $game['matchday'];

            echo "Date: {$date}, Game:{$id}\n";

            if (!$score) {
                echo "No scores.\n";
                continue;
            }

            $hasResults = GameComposer::hasResults($game);

            if (!$hasResults) return;

            $counts++;

            $winningSide = GameComposer::winningSide($game, true);

            if ($winningSide === 0) {
                $full_time_home_wins_counts++;
            } elseif ($winningSide === 1) {
                $full_time_draws_counts++;
            } elseif ($winningSide === 2) {
                $full_time_away_wins_counts++;
            }

            $winningSide = GameComposer::winningSideHT($game, true);

            if ($winningSide === 0) {
                $half_time_home_wins_counts++;
            } elseif ($winningSide === 1) {
                $half_time_draws_counts++;
            } elseif ($winningSide === 2) {
                $half_time_away_wins_counts++;
            }

            $bts = GameComposer::bts($game, true);

            if ($bts) {
                $gg_counts++;
            } else {
                $ng_counts++;
            }

            $goals = GameComposer::goals($game, true);

            if ($goals > 1) {
                $over15_counts++;
            } else {
                $under15_counts++;
            }
            if ($goals > 2) {
                $over25_counts++;
            } else {
                $under25_counts++;
            }
            if ($goals > 3) {
                $over35_counts++;
            } else {
                $under35_counts++;
            }
        }

        if ($counts > 0) {
            $full_time_home_wins_counts = round($full_time_home_wins_counts / $counts * 100);
            $full_time_draws_counts = round($full_time_draws_counts / $counts * 100);
            $full_time_away_wins_counts = round($full_time_away_wins_counts / $counts * 100);
            $half_time_home_wins_counts = round($half_time_home_wins_counts / $counts * 100);
            $half_time_draws_counts = round($half_time_draws_counts / $counts * 100);
            $half_time_away_wins_counts = round($half_time_away_wins_counts / $counts * 100);
            $gg_counts = round($gg_counts / $counts * 100);
            $ng_counts = round($ng_counts / $counts * 100);
            $over15_counts = round($over15_counts / $counts * 100);
            $under15_counts = round($under15_counts / $counts * 100);
            $over25_counts = round($over25_counts / $counts * 100);
            $under25_counts = round($under25_counts / $counts * 100);
            $over35_counts = round($over35_counts / $counts * 100);
            $under35_counts = round($under35_counts / $counts * 100);

            if (!request()->date) {
                CompetitionStatistic::updateOrCreate(
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                    ],
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                        'counts' => $counts,
                        'half_time_home_wins' => $half_time_home_wins_counts,
                        'half_time_draws' => $half_time_draws_counts,
                        'half_time_away_wins' => $half_time_away_wins_counts,
                        'full_time_home_wins' => $full_time_home_wins_counts,
                        'full_time_draws' => $full_time_draws_counts,
                        'full_time_away_wins' => $full_time_away_wins_counts,
                        'gg' => $gg_counts,
                        'ng' => $ng_counts,
                        'over15' => $over15_counts,
                        'under15' => $under15_counts,
                        'over25' => $over25_counts,
                        'under25' => $under25_counts,
                        'over35' => $over35_counts,
                        'under35' => $under35_counts,
                    ]
                );
            } else {

                $date = Carbon::parse(request()->date)->format('Y-m-d');

                MatchdayCompetitionStatistic::updateOrCreate(
                    [
                        'competition_id' => $competition_id,
                        'date' => $date,
                        'matchday' => $matchday,
                    ],
                    [
                        'competition_id' => $competition_id,
                        'date' => $date,
                        'matchday' => $matchday,
                        'counts' => $counts,
                        'half_time_home_wins' => $half_time_home_wins_counts,
                        'half_time_draws' => $half_time_draws_counts,
                        'half_time_away_wins' => $half_time_away_wins_counts,
                        'full_time_home_wins' => $full_time_home_wins_counts,
                        'full_time_draws' => $full_time_draws_counts,
                        'full_time_away_wins' => $full_time_away_wins_counts,
                        'gg' => $gg_counts,
                        'ng' => $ng_counts,
                        'over15' => $over15_counts,
                        'under15' => $under15_counts,
                        'over25' => $over25_counts,
                        'under25' => $under25_counts,
                        'over35' => $over35_counts,
                        'under35' => $under35_counts,
                    ]
                );
            }
        }
    }
}
