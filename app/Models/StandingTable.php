<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StandingTable extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'standing_id',
        'team_id',
        'season_id',

        'position',
        'team_id',
        'played_games',
        'form',
        'won',
        'draw',
        'lost',
        'points',
        'goals_for',
        'goals_against',
        'goal_difference',
    ];

    public function standings()
    {
        return $this->belongsTo(Standings::class, 'standing_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
