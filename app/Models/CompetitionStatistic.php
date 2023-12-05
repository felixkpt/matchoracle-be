<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionStatistic extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'competition_id',
        'season_id',
        'date',
        'matchday',
        'counts',

        'half_time_home_wins',
        'half_time_draws',
        'half_time_away_wins',
        'full_time_home_wins',
        'full_time_draws',
        'full_time_away_wins',
        'gg',
        'ng',
        'over15',
        'under15',
        'over25',
        'under25',
        'over35',
        'under35',
        'status_id',
        'user_id',
    ];
}
