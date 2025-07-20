<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionPredictionStatistic extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'competition_id',
        'season_id',
        'prediction_type_id',
        'date',
        'matchday',

        'ft_counts',
        'ft_home_wins_counts',
        'ft_home_wins_preds',
        'ft_home_wins_preds_true',
        'ft_home_wins_preds_true_percentage',

        'ft_draws_counts',
        'ft_draws_preds',
        'ft_draws_preds_true',
        'ft_draws_preds_true_percentage',

        'ft_away_wins_counts',
        'ft_away_wins_preds',
        'ft_away_wins_preds_true',
        'ft_away_wins_preds_true_percentage',

        'ft_gg_counts',
        'ft_gg_preds',
        'ft_gg_preds_true',
        'ft_gg_preds_true_percentage',

        'ft_ng_counts',
        'ft_ng_preds',
        'ft_ng_preds_true',
        'ft_ng_preds_true_percentage',

        'ft_over15_counts',
        'ft_over15_preds',
        'ft_over15_preds_true',
        'ft_over15_preds_true_percentage',

        'ft_under15_counts',
        'ft_under15_preds',
        'ft_under15_preds_true',
        'ft_under15_preds_true_percentage',

        'ft_over25_counts',
        'ft_over25_preds',
        'ft_over25_preds_true',
        'ft_over25_preds_true_percentage',

        'ft_under25_counts',
        'ft_under25_preds',
        'ft_under25_preds_true',
        'ft_under25_preds_true_percentage',

        'ft_over35_counts',
        'ft_over35_preds',
        'ft_over35_preds_true',
        'ft_over35_preds_true_percentage',

        'ft_under35_counts',
        'ft_under35_preds',
        'ft_under35_preds_true',
        'ft_under35_preds_true_percentage',

        'ht_counts',
        'ht_home_wins_counts',
        'ht_home_wins_preds',
        'ht_home_wins_preds_true',
        'ht_home_wins_preds_true_percentage',

        'ht_draws_counts',
        'ht_draws_preds',
        'ht_draws_preds_true',
        'ht_draws_preds_true_percentage',

        'ht_away_wins_counts',
        'ht_away_wins_preds',
        'ht_away_wins_preds_true',
        'ht_away_wins_preds_true_percentage',

        'ht_gg_counts',
        'ht_gg_preds',
        'ht_gg_preds_true',
        'ht_gg_preds_true_percentage',

        'ht_ng_counts',
        'ht_ng_preds',
        'ht_ng_preds_true',
        'ht_ng_preds_true_percentage',

        'ht_over15_counts',
        'ht_over15_preds',
        'ht_over15_preds_true',
        'ht_over15_preds_true_percentage',

        'ht_under15_counts',
        'ht_under15_preds',
        'ht_under15_preds_true',
        'ht_under15_preds_true_percentage',

        'ht_over25_counts',
        'ht_over25_preds',
        'ht_over25_preds_true',
        'ht_over25_preds_true_percentage',

        'ht_under25_counts',
        'ht_under25_preds',
        'ht_under25_preds_true',
        'ht_under25_preds_true_percentage',

        'ht_over35_counts',
        'ht_over35_preds',
        'ht_over35_preds_true',
        'ht_over35_preds_true_percentage',

        'ht_under35_counts',
        'ht_under35_preds',
        'ht_under35_preds_true',
        'ht_under35_preds_true_percentage',

        'accuracy_score',
        'precision_score',
        'f1_score',
        'average_score',

        'status_id',
        'user_id',

    ];
}
