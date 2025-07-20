<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamePrediction extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'version',
        'prediction_type_id',
        'competition_id',
        'date',
        'game_id',
        
        'ft_hda_pick',
        'ft_home_win_proba',
        'ft_draw_proba',
        'ft_away_win_proba',
        
        'ht_hda_pick',
        'ht_home_win_proba',
        'ht_draw_proba',
        'ht_away_win_proba',
        
        'bts_pick',
        'gg_proba',
        'ng_proba',

        'over_under15_pick',
        'over15_proba',
        'under15_proba',

        'over_under25_pick',
        'over25_proba',
        'under25_proba',

        'over_under35_pick',
        'over35_proba',
        'under35_proba',
        
        'cs',
        'cs_proba',
        'user_id',
        'status_id',
    ];

    public function score()
    {
        return $this->hasOne(GameScore::class, 'game_id');
    }
}
