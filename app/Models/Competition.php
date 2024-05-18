<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Competition extends Model
{
  use HasFactory, CommonModelRelationShips, ExcludeSystemFillable;

  protected $fillable = [
    'name',
    'slug',
    'code',
    'type',
    'logo',
    'plan',

    'abbreviation',
    'continent_id',
    'country_id',

    'games_per_season',
    'available_seasons',
    'gender',
    'user_id',
    'status_id',
    'has_standings',
    'has_teams',
    'is_odds_enabled',
    'priority_number',

    'games_counts',
    'predictions_counts',
    'odds_counts',

  ];

  protected $systemFillable = [
    'user_id',
    'status_id',
  ];

  public function lastAction()
  {
    return $this->hasOne(CompetitionLastAction::class);
  }

  public function predictionLog()
  {
    return $this->hasOne(CompetitionPredictionLog::class);
  }

  function continent()
  {
    return $this->belongsTo(Continent::class);
  }

  function country()
  {
    return $this->belongsTo(Country::class);
  }

  function teams()
  {
    return $this->hasMany(Team::class);
  }

  function gameSources()
  {
    return $this->belongsToMany(GameSource::class)->withPivot(['source_uri', 'source_id', 'subscription_expires', 'is_subscribed'])->withTimestamps();
  }

  public function matches()
  {
    return $this->hasMany(Game::class);
  }

  public function games()
  {
    return $this->hasMany(Game::class);
  }

  public function currentSeason()
  {
    return $this->hasOne(Season::class, 'competition_id')->where('is_current', true);
  }

  public function season()
  {
    return $this->hasOne(Season::class, 'competition_id')->where('is_current', true);
  }

  public function seasons()
  {
    return $this->hasMany(Season::class, 'competition_id');
  }

  public function stages()
  {
    return $this->hasMany(Stage::class, 'competition_id');
  }

  public function standings()
  {
    return $this->hasMany(Standing::class, 'competition_id');
  }

  public function teamsStandings()
  {
    return $this->hasMany(Standing::class, 'competition_id')->with('standingTable.team');
  }

  public function predictionStatistic()
  {
    return $this->hasMany(CompetitionPredictionStatistic::class);
  }
}
