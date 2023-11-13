<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameVote extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'game_id',
        'winner',
        'over_under',
        'gg_ng',
        'user_ip',
        'user_id',
        'status_id',
        'priority_number',
    ];
}
