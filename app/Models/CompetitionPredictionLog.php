<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionPredictionLog extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'version',
        'prediction_type_id',
        'competition_id',
        'date',

        'total_games',
        'predictable_games',
        'predicted_games',
        'unpredicted_games',
    ];

    function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
