<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionLastAction extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'competition_id',

        'abbreviation_last_fetch',
        
        'seasons_last_fetch',

        'standings_recent_results_last_fetch',
        'standings_historical_results_last_fetch',

        'matches_recent_results_last_fetch',
        'matches_historical_results_last_fetch',
        'matches_fixtures_last_fetch',
        'matches_shallow_fixtures_last_fetch',

        'match_recent_results_last_fetch',
        'match_historical_results_last_fetch',
        'match_fixtures_last_fetch',
        'match_shallow_fixtures_last_fetch',

        'stats_last_done',
        'predictions_stats_last_done',
        
        'predictions_last_train',
        'predictions_trained_to',
        'predictions_last_done',
    ];

    public function lastAction()
    {
        return $this->belongsTo(Competition::class);
    }
}
