<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamePredictionLog extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'date',
        'total_games',
        'predicted_games',
        'unpredicted_games',
    ];
}
