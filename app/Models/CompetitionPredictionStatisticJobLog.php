<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionPredictionStatisticJobLog extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'version',
        'prediction_type_id',
        'date',

        'job_run_counts',
        'competition_counts',
        'run_competition_counts',
        'action_counts',
        'run_action_counts',
        'average_seconds_per_action',
        'created_counts',
        'updated_counts',
        'failed_counts',
    ];
}
