<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'start_date',
        'end_date',
        'current_matchday',
        'total_matchdays',
        'played_matches',
        'competition_id',
        'is_current',
        'winner_id',
        'fetched_standings',
        'fetched_all_matches',
        'fetched_all_single_matches',

        'games_total_count',
        'ft_pending_count',
        'ft_fetched_count',
        'ft_missing_count',
        'ht_pending_count',
        'ht_fetched_count',
        'ht_missing_count',
        'odd_ft_pending_count',
        'odd_ft_fetched_count',
        'odd_ft_missing_count',
        'odd_ht_pending_count',
        'odd_ht_fetched_count',
        'odd_ht_missing_count',

        'status_id',
        'user_id',
    ];

    protected $casts = [
        'stages' => 'json',
    ];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function standings()
    {
        return $this->hasMany(Standing::class);
    }

    public function winner()
    {
        return $this->belongsTo(Team::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }
}
