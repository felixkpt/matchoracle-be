<?php

namespace App\Repositories\Statistics;

use App\Models\CompetitionPredictionStatistic;
use App\Models\Game;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\Game\GameRepository;
use App\Repositories\Game\GameRepositoryInterface;
use App\Utilities\GamePredictionStatsUtility;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CompetitionPredictionStatisticsRepository implements CompetitionPredictionStatisticsRepositoryInterface
{

    use CommonRepoActions;

    function __construct(
        protected CompetitionPredictionStatistic $model
    ) {
    }

    function index()
    {

        sleep(0);

        $results = $this->model
            ->where('prediction_type_id', current_prediction_type())
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
            'prediction_type_id' => current_prediction_type(), 'per_page' => 5000,
            'order_by' => 'utc_date', 'order_direction' => 'asc', 'to_date' => Carbon::now()->format('Y-m-d'),
            'without_response' => true,
            'show_predictions' => true,
        ]);


        $games = (new GameRepository(new Game()))->index(null, true);

        $games = $games['results']['data'];
        $ct = count($games);

        $unique_dates_counts = count(array_unique(
            array_map(fn ($c) => Carbon::parse($c)->format('Y-m-d'), array_column($games, 'utc_date'))
        ));
        // Log::info("Unique date counts: {$unique_dates_counts}");


        $prediction_type_id = request()->prediction_type_id;
        $arr = (new GamePredictionStatsUtility())->doStats($games);

        $counts = $arr['counts'];
        if ($arr && $counts > 0) {

            $ftArr = $arr['ft'];
            $htArr = $arr['ht'];

            $arr = [
                'counts' => $counts,

                'ft_home_wins_counts' => $ftArr['home_wins']['counts'],
                'ft_home_wins_preds' => $ftArr['home_wins']['preds'],
                'ft_home_wins_preds_true' => $ftArr['home_wins']['preds_true'],
                'ft_home_wins_preds_true_percentage' => $ftArr['home_wins']['preds_true_percentage'],

                'ft_draws_counts' => $ftArr['draws']['counts'],
                'ft_draws_preds' => $ftArr['draws']['preds'],
                'ft_draws_preds_true' => $ftArr['draws']['preds_true'],
                'ft_draws_preds_true_percentage' => $ftArr['draws']['preds_true_percentage'],

                'ft_away_wins_counts' => $ftArr['away_wins']['counts'],
                'ft_away_wins_preds' => $ftArr['away_wins']['preds'],
                'ft_away_wins_preds_true' => $ftArr['away_wins']['preds_true'],
                'ft_away_wins_preds_true_percentage' => $ftArr['away_wins']['preds_true_percentage'],

                'ft_gg_counts' => $ftArr['gg']['counts'],
                'ft_gg_preds' => $ftArr['gg']['preds'],
                'ft_gg_preds_true' => $ftArr['gg']['preds_true'],
                'ft_gg_preds_true_percentage' => $ftArr['gg']['preds_true_percentage'],

                'ft_ng_counts' => $ftArr['ng']['counts'],
                'ft_ng_preds' => $ftArr['ng']['preds'],
                'ft_ng_preds_true' => $ftArr['ng']['preds_true'],
                'ft_ng_preds_true_percentage' => $ftArr['ng']['preds_true_percentage'],

                'ft_over15_counts' => $ftArr['over15']['counts'],
                'ft_over15_preds' => $ftArr['over15']['preds'],
                'ft_over15_preds_true' => $ftArr['over15']['preds_true'],
                'ft_over15_preds_true_percentage' => $ftArr['over15']['preds_true_percentage'],

                'ft_under15_counts' => $ftArr['under15']['counts'],
                'ft_under15_preds' => $ftArr['under15']['preds'],
                'ft_under15_preds_true' => $ftArr['under15']['preds_true'],
                'ft_under15_preds_true_percentage' => $ftArr['under15']['preds_true_percentage'],

                'ft_over25_counts' => $ftArr['over25']['counts'],
                'ft_over25_preds' => $ftArr['over25']['preds'],
                'ft_over25_preds_true' => $ftArr['over25']['preds_true'],
                'ft_over25_preds_true_percentage' => $ftArr['over25']['preds_true_percentage'],

                'ft_under25_counts' => $ftArr['under25']['counts'],
                'ft_under25_preds' => $ftArr['under25']['preds'],
                'ft_under25_preds_true' => $ftArr['under25']['preds_true'],
                'ft_under25_preds_true_percentage' => $ftArr['under25']['preds_true_percentage'],

                'ft_over35_counts' => $ftArr['over35']['counts'],
                'ft_over35_preds' => $ftArr['over35']['preds'],
                'ft_over35_preds_true' => $ftArr['over35']['preds_true'],
                'ft_over35_preds_true_percentage' => $ftArr['over35']['preds_true_percentage'],

                'ft_under35_counts' => $ftArr['under35']['counts'],
                'ft_under35_preds' => $ftArr['under35']['preds'],
                'ft_under35_preds_true' => $ftArr['under35']['preds_true'],
                'ft_under35_preds_true_percentage' => $ftArr['under35']['preds_true_percentage'],

                'ht_home_wins_counts' => $htArr['home_wins']['counts'],
                'ht_home_wins_preds' => $htArr['home_wins']['preds'],
                'ht_home_wins_preds_true' => $htArr['home_wins']['preds_true'],
                'ht_home_wins_preds_true_percentage' => $htArr['home_wins']['preds_true_percentage'],

                'ht_draws_counts' => $htArr['draws']['counts'],
                'ht_draws_preds' => $htArr['draws']['preds'],
                'ht_draws_preds_true' => $htArr['draws']['preds_true'],
                'ht_draws_preds_true_percentage' => $htArr['draws']['preds_true_percentage'],

                'ht_away_wins_counts' => $htArr['away_wins']['counts'],
                'ht_away_wins_preds' => $htArr['away_wins']['preds'],
                'ht_away_wins_preds_true' => $htArr['away_wins']['preds_true'],
                'ht_away_wins_preds_true_percentage' => $htArr['away_wins']['preds_true_percentage'],

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

        $arr = ['message' => 'Total matches ' . $ct . ', successfully done predictions stats., (updated ' . $counts . ').', 'results' => ['updated' => $counts]];

        if (request()->without_response) return $arr;
        return response($arr);
    }
}
