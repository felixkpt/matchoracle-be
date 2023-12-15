<?php

namespace App\Repositories\Statistics;

use App\Models\CompetitionPredictionStatistic;
use App\Models\Game;
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
        protected CompetitionPredictionStatistic $model,
        protected GameRepositoryInterface $gameRepositoryInterface
    ) {
    }

    function index()
    {

        $results = $this->model
            ->where('prediction_type_id', request()->prediction_type_id ?? 2)
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

        request()->merge(['prediction_type_id' => request()->prediction_type_id ?? 2, 'per_page' => 2000, 'order_by' => 'utc_date', 'order_direction' => 'asc', 'to_date' => Carbon::now()->format('Y-m-d')]);

        $games = $this->gameRepositoryInterface->index(null, true);

        $counts = 0;

        // ft vars
        $full_time_home_wins_counts = 0;
        $full_time_draws_counts = 0;
        $full_time_away_wins_counts = 0;
        $full_time_home_wins_preds = 0;
        $full_time_home_wins_preds_true = 0;
        $full_time_draws_preds = 0;
        $full_time_draws_preds_true = 0;
        $full_time_away_wins_preds = 0;
        $full_time_away_wins_preds_true = 0;

        // ht vars
        $half_time_home_wins_counts = 0;
        $half_time_draws_counts = 0;
        $half_time_away_wins_counts = 0;
        // preds vars
        $half_time_home_wins_preds = 0;
        $half_time_home_wins_preds_true = 0;
        $half_time_draws_preds = 0;
        $half_time_draws_preds_true = 0;
        $half_time_away_wins_preds = 0;
        $half_time_away_wins_preds_true = 0;

        // gg vars
        $full_time_gg_counts = 0;
        $full_time_gg_preds = 0;
        $full_time_gg_preds_true = 0;
        // ng vars
        $full_time_ng_counts = 0;
        $full_time_ng_preds = 0;
        $full_time_ng_preds_true = 0;

        // goals vars
        $full_time_over15_counts = 0;
        $full_time_under15_counts = 0;
        $full_time_over25_counts = 0;
        $full_time_under25_counts = 0;
        $full_time_over35_counts = 0;
        $full_time_under35_counts = 0;
        // preds vars
        $full_time_over15_preds = 0;
        $full_time_over15_preds_true = 0;
        $full_time_under15_preds = 0;
        $full_time_under15_preds_true = 0;

        $full_time_over25_preds = 0;
        $full_time_over25_preds_true = 0;
        $full_time_under25_preds = 0;
        $full_time_under25_preds_true = 0;

        $full_time_over35_preds = 0;
        $full_time_over35_preds_true = 0;
        $full_time_under35_preds = 0;
        $full_time_under35_preds_true = 0;

        $games = $games['data'];
        $ct = count($games);

        echo "Total games: $ct\n\n";

        $unique_dates_counts = count(array_unique(array_map(fn ($c) => Carbon::parse($c)->format('Y-m-d'), array_column($games, 'utc_date'))));
        Log::info("Unique date counts: {$unique_dates_counts}");

        $matchday = 0;
        foreach ($games as $game) {
            $prediction = $game['prediction'];
            if (!$prediction) continue;

            $id = $game['id'];
            $date = $game['utc_date'];
            $score = $game['score'];
            $matchday = $game['matchday'];

            echo "Date: {$date}, Game:{$id}\n";

            if (!$score) {
                echo "No scores.\n";
                continue;
            }

            echo "FT: " . $score['home_scores_full_time'] . " - " . $score['away_scores_full_time'] . "\n";

            $hasResults = GameComposer::hasResults($game);

            if (!$hasResults) continue;

            $prediction_type_id = $prediction['prediction_type_id'];

            $counts++;

            $winningSide = GameComposer::winningSide($game, true);

            if ($winningSide === 0) {
                $full_time_home_wins_counts++;
            } elseif ($winningSide === 1) {
                $full_time_draws_counts++;
            } elseif ($winningSide === 2) {
                $full_time_away_wins_counts++;
            }

            // handle preds
            if ($prediction['hda'] === 0) {
                $full_time_home_wins_preds++;
                if ($winningSide === 0) {
                    $full_time_home_wins_preds_true++;
                }
            } elseif ($prediction['hda'] === 1) {
                $full_time_draws_preds++;
                if ($winningSide === 1) {
                    $full_time_draws_preds_true++;
                }
            } elseif ($prediction['hda'] === 2) {
                $full_time_away_wins_preds++;
                if ($winningSide === 2) {
                    $full_time_away_wins_preds_true++;
                }
            }

            // handle halftime
            $winningSide = GameComposer::winningSideHT($game, true);

            if ($winningSide === 0) {
                $half_time_home_wins_counts++;
            } elseif ($winningSide === 1) {
                $half_time_draws_counts++;
            } elseif ($winningSide === 2) {
                $half_time_away_wins_counts++;
            }


            // handle preds
            if ($prediction['hda'] === 0) {
                $half_time_home_wins_preds++;
                if ($winningSide === 0) {
                    $half_time_home_wins_preds_true++;
                }
            } elseif ($prediction['hda'] === 1) {
                $half_time_draws_preds++;
                if ($winningSide === 1) {
                    $half_time_draws_preds_true++;
                }
            } elseif ($prediction['hda'] === 2) {
                $half_time_away_wins_preds++;
                if ($winningSide === 2) {
                    $half_time_away_wins_preds_true++;
                }
            }

            $bts = GameComposer::bts($game, true);

            if ($bts) {
                $full_time_gg_counts++;
            } else {
                $full_time_ng_counts++;
            }

            // handle preds
            if ($prediction['bts'] === 1) {
                $full_time_gg_preds++;
                if ($bts === 1) {
                    $full_time_gg_preds_true++;
                }
            } else {
                $full_time_ng_preds++;
                if ($bts === 0) {
                    $full_time_ng_preds_true++;
                }
            }


            $goals = GameComposer::goals($game, true);

            if ($goals > 1) {
                $full_time_over15_counts++;
            } else {
                $full_time_under15_counts++;
            }
            if ($goals > 2) {
                $full_time_over25_counts++;
            } else {
                $full_time_under25_counts++;
            }
            if ($goals > 3) {
                $full_time_over35_counts++;
            } else {
                $full_time_under35_counts++;
            }

            // handle preds 1.5
            if ($prediction['over15'] === 1) {
                $full_time_over15_preds++;
                if ($goals > 1) {
                    $full_time_over15_preds_true++;
                }
            } else {
                $full_time_under15_preds++;
                if ($goals <= 2) {
                    $full_time_under15_preds_true++;
                }
            }

            // handle preds 2.5
            if ($prediction['over25'] === 1) {
                $full_time_over25_preds++;
                if ($goals > 2) {
                    $full_time_over25_preds_true++;
                }
            } else {
                $full_time_under25_preds++;
                if ($goals <= 2) {
                    $full_time_under25_preds_true++;
                }
            }

            // handle preds 3.5
            if ($prediction['over35'] === 1) {
                $full_time_over35_preds++;
                if ($goals > 3) {
                    $full_time_over35_preds_true++;
                }
            } else {
                $full_time_under35_preds++;
                if ($goals <= 3) {
                    $full_time_under35_preds_true++;
                }
            }
        }

        if ($counts > 0) {
            $full_time_home_wins_preds_true_percentage = $full_time_home_wins_preds == 0 ? 0 : round($full_time_home_wins_preds_true / $full_time_home_wins_preds * 100);
            $full_time_draws_preds_true_percentage = $full_time_draws_preds == 0 ? 0 : round($full_time_draws_preds_true / $full_time_draws_preds * 100);
            $full_time_away_wins_preds_true_percentage = $full_time_away_wins_preds == 0 ? 0 : round($full_time_away_wins_preds_true / $full_time_away_wins_preds * 100);

            $full_time_gg_preds_true_percentage = $full_time_gg_preds == 0 ? 0 : round($full_time_gg_preds_true / $full_time_gg_preds * 100);
            $full_time_ng_preds_true_percentage = $full_time_ng_preds == 0 ? 0 : round($full_time_ng_preds_true / $full_time_ng_preds * 100);

            $full_time_over15_preds_true_percentage = $full_time_over15_preds == 0 ? 0 : round($full_time_over15_preds_true / $full_time_over15_preds * 100);
            $full_time_under15_preds_true_percentage = $full_time_under15_preds == 0 ? 0 : round($full_time_under15_preds_true / $full_time_under15_preds * 100);

            $full_time_over25_preds_true_percentage = $full_time_over25_preds == 0 ? 0 : round($full_time_over25_preds_true / $full_time_over25_preds * 100);
            $full_time_under25_preds_true_percentage = $full_time_under25_preds == 0 ? 0 : round($full_time_under25_preds_true / $full_time_under25_preds * 100);

            $full_time_over35_preds_true_percentage = $full_time_over35_preds == 0 ? 0 : round($full_time_over35_preds_true / $full_time_over35_preds * 100);
            $full_time_under35_preds_true_percentage = $full_time_under35_preds == 0 ? 0 : round($full_time_under35_preds_true / $full_time_under35_preds * 100);


            // Calculate overall accuracy score
            $totalCorrectPredictions = $full_time_home_wins_preds_true +
                $full_time_draws_preds_true +
                $full_time_away_wins_preds_true +
                $full_time_ng_preds_true +
                $full_time_gg_preds_true +
                $full_time_over15_preds_true +
                $full_time_under15_preds_true +
                $full_time_over25_preds_true +
                $full_time_under25_preds_true +
                $full_time_over35_preds_true +
                $full_time_under35_preds_true;

            $totalPredictions = $full_time_home_wins_preds +
                $full_time_draws_preds +
                $full_time_away_wins_preds +
                $full_time_ng_preds +
                $full_time_gg_preds +
                $full_time_over15_preds +
                $full_time_under15_preds +
                $full_time_over25_preds +
                $full_time_under25_preds +
                $full_time_over35_preds +
                $full_time_under35_preds;

            $average_score = $totalPredictions == 0 ? 0 : round($totalCorrectPredictions / $totalPredictions * 100);

            $arr = [
                'counts' => $counts,

                'full_time_home_wins_preds' => $full_time_home_wins_preds,
                'full_time_home_wins_preds_true' => $full_time_home_wins_preds_true,
                'full_time_home_wins_preds_true_percentage' => $full_time_home_wins_preds_true_percentage,

                'full_time_draws_preds' => $full_time_draws_preds,
                'full_time_draws_preds_true' => $full_time_draws_preds_true,
                'full_time_draws_preds_true_percentage' => $full_time_draws_preds_true_percentage,

                'full_time_away_wins_preds' => $full_time_away_wins_preds,
                'full_time_away_wins_preds_true' => $full_time_away_wins_preds_true,
                'full_time_away_wins_preds_true_percentage' => $full_time_away_wins_preds_true_percentage,

                'full_time_ng_preds' => $full_time_ng_preds,
                'full_time_ng_preds_true' => $full_time_ng_preds_true,
                'full_time_ng_preds_true_percentage' => $full_time_ng_preds_true_percentage,

                'full_time_gg_preds' => $full_time_gg_preds,
                'full_time_gg_preds_true' => $full_time_gg_preds_true,
                'full_time_gg_preds_true_percentage' => $full_time_gg_preds_true_percentage,

                'full_time_over15_preds' => $full_time_over15_preds,
                'full_time_over15_preds_true' => $full_time_over15_preds_true,
                'full_time_over15_preds_true_percentage' => $full_time_over15_preds_true_percentage,

                'full_time_under15_preds' => $full_time_under15_preds,
                'full_time_under15_preds_true' => $full_time_under15_preds_true,
                'full_time_under15_preds_true_percentage' => $full_time_under15_preds_true_percentage,

                'full_time_over25_preds' => $full_time_over25_preds,
                'full_time_over25_preds_true' => $full_time_over25_preds_true,
                'full_time_over25_preds_true_percentage' => $full_time_over25_preds_true_percentage,

                'full_time_under25_preds' => $full_time_under25_preds,
                'full_time_under25_preds_true' => $full_time_under25_preds_true,
                'full_time_under25_preds_true_percentage' => $full_time_under25_preds_true_percentage,

                'full_time_over35_preds' => $full_time_over35_preds,
                'full_time_over35_preds_true' => $full_time_over35_preds_true,
                'full_time_over35_preds_true_percentage' => $full_time_over35_preds_true_percentage,

                'full_time_under35_preds' => $full_time_under35_preds,
                'full_time_under35_preds_true' => $full_time_under35_preds_true,
                'full_time_under35_preds_true_percentage' => $full_time_under35_preds_true_percentage,
                'average_score' => $average_score,

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

            dump('end');
        }
    }
}
