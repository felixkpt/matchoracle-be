<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odd extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'utc_date',
        'has_time',
        'home_team',
        'away_team',
        'home_win_odds',
        'draw_odds',
        'away_win_odds',
        'over_25_odds',
        'under_25_odds',
        'gg_odds',
        'ng_odds',
        'game_id',
        'source_id',
    ];

    function game() {
        return $this->belongsTo(Game::class);
    }
}
