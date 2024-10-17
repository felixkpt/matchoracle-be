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

        'ht_home_wins',
        'ht_draws',
        'ht_away_wins',
        'ft_home_wins',
        'ft_draws',
        'ft_away_wins',
        'ft_gg',
        'ft_ng',
        'ft_over15',
        'ft_under15',
        'ft_over25',
        'ft_under25',
        'ft_over35',
        'ft_under35',

        'status_id',
        'user_id',
    ];
}
