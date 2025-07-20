<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BettingTipsStatistic extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'type',
        'is_multiples',
        'range',

        'initial_bankroll',
        'bankroll_topups',
        'final_bankroll',

        'total',
        'won',
        'won_percentage',
        'average_won_odds',
        'gain',
        'roi',
        'longest_winning_streak',
        'longest_losing_streak',

        'status_id',
        'user_id',

    ];
}
