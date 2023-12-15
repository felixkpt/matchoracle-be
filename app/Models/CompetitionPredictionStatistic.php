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
        'counts',

        'full_time_home_wins_preds',
        'full_time_home_wins_preds_true',
        'full_time_home_wins_preds_true_percentage',

        'full_time_draws_preds',
        'full_time_draws_preds_true',
        'full_time_draws_preds_true_percentage',

        'full_time_away_wins_preds',
        'full_time_away_wins_preds_true',
        'full_time_away_wins_preds_true_percentage',

        'full_time_gg_preds',
        'full_time_gg_preds_true',
        'full_time_gg_preds_true_percentage',

        'full_time_ng_preds',
        'full_time_ng_preds_true',
        'full_time_ng_preds_true_percentage',

        'full_time_over15_preds',
        'full_time_over15_preds_true',
        'full_time_over15_preds_true_percentage',

        'full_time_under15_preds',
        'full_time_under15_preds_true',
        'full_time_under15_preds_true_percentage',

        'full_time_over25_preds',
        'full_time_over25_preds_true',
        'full_time_over25_preds_true_percentage',

        'full_time_under25_preds',
        'full_time_under25_preds_true',
        'full_time_under25_preds_true_percentage',

        'full_time_over35_preds',
        'full_time_over35_preds_true',
        'full_time_over35_preds_true_percentage',

        'full_time_under35_preds',
        'full_time_under35_preds_true',
        'full_time_under35_preds_true_percentage',

        'accuracy_score',
        'precision_score',
        'f1_score',
        'average_score',

        'status_id',
        'user_id',

    ];
}
