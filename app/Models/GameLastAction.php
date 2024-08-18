<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameLastAction extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'game_id',

        'match_recent_results_last_fetch',
        'match_historical_results_last_fetch',
        'match_fixtures_last_fetch',
        'match_shallow_fixtures_last_fetch',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
