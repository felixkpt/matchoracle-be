<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionScoreTargetOutcome extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'prediction_type',
        'competition_id',
        'train_counts',
        'test_counts',
        'score_target_outcome_id',
        'occurrences',
        'last_predicted',
        'accuracy_score',
        'precision_score',
        'f1_score',
        'average_score',
        'from_date',
        'to_date',

        'user_id',
        'status_id',
    ];
}
