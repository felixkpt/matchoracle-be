<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BettingStrategyProCon extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name', 'type',
        'position',
        'status_id',
        'user_id',
    ];
}
