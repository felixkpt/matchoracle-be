<?php

namespace App\Repositories\Statistics;

use App\Models\CompetitionPredictionStatistic;
use App\Models\Game;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\Game\GameRepositoryInterface;
use App\Utilities\GamePredictionStatsUtility;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CompetitionPredictionStatisticsRepository implements CompetitionPredictionStatisticsRepositoryInterface
{

    use CommonRepoActions;

    function __construct(
        protected CompetitionPredictionStatistic $model,
        protected GameRepositoryInterface $gameRepositoryInterface
    ) {
    }

    function index()
    {

        sleep(0);

        $results = $this->model
            ->where('prediction_type_id', default_prediction_type())
            ->where('competition_id', request()->competition_id)
            ->when(request()->season_id, fn ($q) => $q->where('season_id', request()->season_id))
            ->when(request()->from_date, fn ($q) => $q->whereDate('date', '>=', Carbon::parse(request()->from_date)->format('Y-m-d')))
            ->when(request()->to_date, fn ($q) => $q->whereDate('date', '<=', Carbon::parse(request()->to_date)->format('Y-m-d')))
            ->first();

        return response(['results' => $results]);
    }

    public function store()
    {

        $competition_id = request()->competition_id;
        $season = Season::find(request()->season_id);
        $season_id = $season->id ?? 0;

        request()->merge([
            'prediction_type_id' => request()->prediction_type_id ?? 2, 'per_page' => 5000,
            'order_by' => 'utc_date', 'order_direction' => 'asc', 'to_date' => Carbon::now()->format('Y-m-d'),
            'without_response' => true,
            'show_predictions' => true,
        ]);


        $games = $this->gameRepositoryInterface->index(null, true);

        $games = $games['results']['data'];

        $unique_dates_counts = count(array_unique(
            array_map(fn ($c) => Carbon::parse($c)->format('Y-m-d'), array_column($games, 'utc_date'))
        ));
        // Log::info("Unique date counts: {$unique_dates_counts}");


        $prediction_type_id = request()->prediction_type_id;
        $arr = (new GamePredictionStatsUtility())->doStats($games);

        if ($arr && $arr['counts'] > 0) {

            $fullTimeArr = $arr['full_time'];
            $halfTimeArr = $arr['half_time'];

            $arr = [
                'counts' => $arr['counts'],

                'full_time_home_wins_counts' => $fullTimeArr['home_wins']['counts'],
                'full_time_home_wins_preds' => $fullTimeArr['home_wins']['preds'],
                'full_time_home_wins_preds_true' => $fullTimeArr['home_wins']['preds_true'],
                'full_time_home_wins_preds_true_percentage' => $fullTimeArr['home_wins']['preds_true_percentage'],

                'full_time_draws_counts' => $fullTimeArr['draws']['counts'],
                'full_time_draws_preds' => $fullTimeArr['draws']['preds'],
                'full_time_draws_preds_true' => $fullTimeArr['draws']['preds_true'],
                'full_time_draws_preds_true_percentage' => $fullTimeArr['draws']['preds_true_percentage'],

                'full_time_away_wins_counts' => $fullTimeArr['away_wins']['counts'],
                'full_time_away_wins_preds' => $fullTimeArr['away_wins']['preds'],
                'full_time_away_wins_preds_true' => $fullTimeArr['away_wins']['preds_true'],
                'full_time_away_wins_preds_true_percentage' => $fullTimeArr['away_wins']['preds_true_percentage'],

                'full_time_gg_counts' => $fullTimeArr['gg']['counts'],
                'full_time_gg_preds' => $fullTimeArr['gg']['preds'],
                'full_time_gg_preds_true' => $fullTimeArr['gg']['preds_true'],
                'full_time_gg_preds_true_percentage' => $fullTimeArr['gg']['preds_true_percentage'],

                'full_time_ng_counts' => $fullTimeArr['ng']['counts'],
                'full_time_ng_preds' => $fullTimeArr['ng']['preds'],
                'full_time_ng_preds_true' => $fullTimeArr['ng']['preds_true'],
                'full_time_ng_preds_true_percentage' => $fullTimeArr['ng']['preds_true_percentage'],

                'full_time_over15_counts' => $fullTimeArr['over15']['counts'],
                'full_time_over15_preds' => $fullTimeArr['over15']['preds'],
                'full_time_over15_preds_true' => $fullTimeArr['over15']['preds_true'],
                'full_time_over15_preds_true_percentage' => $fullTimeArr['over15']['preds_true_percentage'],

                'full_time_under15_counts' => $fullTimeArr['under15']['counts'],
                'full_time_under15_preds' => $fullTimeArr['under15']['preds'],
                'full_time_under15_preds_true' => $fullTimeArr['under15']['preds_true'],
                'full_time_under15_preds_true_percentage' => $fullTimeArr['under15']['preds_true_percentage'],

                'full_time_over25_counts' => $fullTimeArr['over25']['counts'],
                'full_time_over25_preds' => $fullTimeArr['over25']['preds'],
                'full_time_over25_preds_true' => $fullTimeArr['over25']['preds_true'],
                'full_time_over25_preds_true_percentage' => $fullTimeArr['over25']['preds_true_percentage'],

                'full_time_under25_counts' => $fullTimeArr['under25']['counts'],
                'full_time_under25_preds' => $fullTimeArr['under25']['preds'],
                'full_time_under25_preds_true' => $fullTimeArr['under25']['preds_true'],
                'full_time_under25_preds_true_percentage' => $fullTimeArr['under25']['preds_true_percentage'],

                'full_time_over35_counts' => $fullTimeArr['over35']['counts'],
                'full_time_over35_preds' => $fullTimeArr['over35']['preds'],
                'full_time_over35_preds_true' => $fullTimeArr['over35']['preds_true'],
                'full_time_over35_preds_true_percentage' => $fullTimeArr['over35']['preds_true_percentage'],

                'full_time_under35_counts' => $fullTimeArr['under35']['counts'],
                'full_time_under35_preds' => $fullTimeArr['under35']['preds'],
                'full_time_under35_preds_true' => $fullTimeArr['under35']['preds_true'],
                'full_time_under35_preds_true_percentage' => $fullTimeArr['under35']['preds_true_percentage'],

                'half_time_home_wins_counts' => $halfTimeArr['home_wins']['counts'],
                'half_time_home_wins_preds' => $halfTimeArr['home_wins']['preds'],
                'half_time_home_wins_preds_true' => $halfTimeArr['home_wins']['preds_true'],
                'half_time_home_wins_preds_true_percentage' => $halfTimeArr['home_wins']['preds_true_percentage'],

                'half_time_draws_counts' => $halfTimeArr['draws']['counts'],
                'half_time_draws_preds' => $halfTimeArr['draws']['preds'],
                'half_time_draws_preds_true' => $halfTimeArr['draws']['preds_true'],
                'half_time_draws_preds_true_percentage' => $halfTimeArr['draws']['preds_true_percentage'],

                'half_time_away_wins_counts' => $halfTimeArr['away_wins']['counts'],
                'half_time_away_wins_preds' => $halfTimeArr['away_wins']['preds'],
                'half_time_away_wins_preds_true' => $halfTimeArr['away_wins']['preds_true'],
                'half_time_away_wins_preds_true_percentage' => $halfTimeArr['away_wins']['preds_true_percentage'],

                'average_score' => $arr['average_score'],

            ];


            if ($season_id && (!request()->date || $unique_dates_counts > 1)) {
                CompetitionPredictionStatistic::updateOrCreate(
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                        'prediction_type_id' => $prediction_type_id,
                    ],
                    array_merge(
                        [
                            'competition_id' => $competition_id,
                            'season_id' => $season_id,
                            'prediction_type_id' => $prediction_type_id,
                        ],
                        $arr
                    )
                );
            } else {

                $game = null;
                $matchday = null;
                $counts = 0;
                $date = Carbon::parse(request()->date)->format('Y-m-d');

                if (!$season_id) {

                    $game = Game::find($game['id']);
                    $season_id = $game->season_id ?? 0;
                }

                CompetitionPredictionStatistic::updateOrCreate(
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                        'date' => $date,
                        'matchday' => $matchday,
                    ],
                    array_merge(
                        [
                            'competition_id' => $competition_id,
                            'season_id' => $season_id,
                            'date' => $date,
                            'matchday' => $matchday,
                            'counts' => $counts,
                        ],
                        $arr
                    )
                );
            }
        }

        $arr = ['message' => 'Successfully done predictions stats.', 'results' => ['updated' => $arr['counts'] ?? 0]];
        if (request()->without_response) return $arr;
        return response($arr);
    }
}
