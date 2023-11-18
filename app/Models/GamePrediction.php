<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamePrediction extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'version',
        'type',
        'competition_id',
        'date',
        'game_id',
        'hda',
        'home_win_proba',
        'draw_proba',
        'away_win_proba',
        'bts',
        'gg_proba',
        'ng_proba',
        'over25',
        'over25_proba',
        'under25_proba',
        'cs_unsensored',
        'cs_proba_unsensored',
        'cs',
        'cs_proba',
    ];
}
