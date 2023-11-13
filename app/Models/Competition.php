<?php

namespace App\Models;

use App\Traits\ExcludeSystemFillable;
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
    'emblem',
    'plan',

    'abbreviation',
    'continent_id',
    'country_id',

    'last_updated',
    'games_per_season',
    'available_seasons',
    'last_fetch',
    'last_detailed_fetch',
    'user_id',
    'status_id',
    'has_teams',
    'priority_number',
  ];

  protected $systemFillable = [
    'user_id',
    'status_id',
  ];

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
    return $this->belongsToMany(GameSource::class)->withPivot(['uri', 'source_id', 'subscription_expires', 'is_subscribed'])->withTimestamps();
  }

  public function matches()
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
}
