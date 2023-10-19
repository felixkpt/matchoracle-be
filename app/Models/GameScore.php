<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameScore extends Model
{
    use HasFactory, HasUlids, CommonModelRelationShips;

    protected $fillable = [
        'game_id',
        'winner',
        'duration',
        'home_scores_half_time',
        'away_scores_half_time',
        
        'home_scores_full_time',
        'away_scores_full_time',

        'status_id',
        'user_id',
    ];
}
