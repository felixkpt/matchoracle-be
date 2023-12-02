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
        'over15',
        'over15_proba',
        'under15_proba',
        'over25',
        'over25_proba',
        'under25_proba',
        'over35',
        'over35_proba',
        'under35_proba',
        'cs',
        'cs_proba',
    ];

    public function score()
    {
        return $this->hasOne(GameScore::class, 'game_id');
    }
}
