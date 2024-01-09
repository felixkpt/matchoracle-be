<?php

namespace App\Models;

use App\Traits\ExcludeSystemFillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory, CommonModelRelationShips, ExcludeSystemFillable;

    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'tla',
        'logo',
        'address_id',
        'website',
        'founded',
        'club_colors',
        'venue_id',
        'coach_id',

        'competition_id',
        'continent_id',
        'country_id',
        'last_updated',
        'last_fetch',
        'last_detailed_fetch',
        'gender',
        'status_id',
        'user_id',
    ];

    protected $systemFillable = ['continent_id', 'last_updated'];

    function gameSources()
    {
        return $this->belongsToMany(GameSource::class)->withPivot(['source_uri', 'source_id', 'subscription_expires', 'is_subscribed'])->withTimestamps();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function matches()
    {
        return $this->hasMany(Game::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function coachContract()
    {
        return $this->hasOne(CoachContract::class);
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'predicted_winner_id');
    }
}
