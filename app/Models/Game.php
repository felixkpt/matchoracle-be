<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'competition_id',
        'home_team_id',
        'away_team_id',
        'season_id',
        'country_id',
        'utc_date',
        'has_time',
        'status',
        'matchday',
        'stage',
        'group',
        'results_status',
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

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function score()
    {
        return $this->hasOne(GameScore::class);
    }

    public function prediction()
    {
        return $this->hasOne(GamePrediction::class)->where('prediction_type_id', default_prediction_type());
    }

    public function gameSources()
    {
        return $this->belongsToMany(GameSource::class)->withPivot(['source_uri', 'source_id'])->withTimestamps();
    }

    public function odds()
    {
        return $this->belongsToMany(Odd::class)->withTimestamps();
    }


    public function referees()
    {
        return $this->belongsToMany(Referee::class)->withTimestamps();
    }

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function votes()
    {
        return $this->hasMany(GameVote::class);
    }
}
