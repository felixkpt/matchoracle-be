<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameLastAction extends Model
{
    use HasFactory, CommonModelRelationShips;

    // Status constants
    const STATUS_PENDING = 0;
    const STATUS_FETCHED = 1;
    const STATUS_MISSING = 2;

    protected $fillable = [
        'game_id',
        'source_id',

        'match_ft_status',
        'match_ht_status',
        'match_recent_results_last_fetch',
        'match_historical_results_last_fetch',
        'match_fixtures_last_fetch',
        'match_shallow_fixtures_last_fetch',

        'odd_ft_status',
        'odd_ht_status',
        'odd_recent_results_last_fetch',
        'odd_historical_results_last_fetch',
        'odd_fixtures_last_fetch',
        'odd_shallow_fixtures_last_fetch',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
