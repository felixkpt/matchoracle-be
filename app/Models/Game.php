<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory, HasUlids, CommonModelRelationShips;

    protected $fillable = [
        'competition_id',
        'home_team_id',
        'away_team_id',
        'season_id',
        'country_id',
        'utc_date',
        'status',
        'matchday',
        'stage',
        'group',
        'game_score_id',
        'last_updated',
        'last_fetch',
        'priority_number',
        'status_id',
        'user_id',
    ];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function score()
    {
        return $this->belongsTo(GameScore::class);
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }

    public function gameSources()
    {
        return $this->belongsToMany(GameSource::class)->withPivot(['uri', 'source_id'])->withTimestamps();
    }
    
    public function referees()
    {
        return $this->belongsToMany(Referee::class)->withTimestamps();
    }
}
