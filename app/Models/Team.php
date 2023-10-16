<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory, HasUlids, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'slug',
        'short_name',
        'tla',
        'country_id',
        'crest',
        'address_id',
        'website',
        'founded',
        'club_colors',
        'venue_id',
        'coach_id',
        
        'url',
        'competition_id',
        'country_id',
        'image',
        'last_updated',
        'last_fetch',
        'last_detailed_fetch',
        'user_id',
        'status_id',
    ];

    protected $searchable = [
        'id',
        'name',
        'slug',
        'url',
        'competition_id',
        'country_id',
        'last_fetch',
        'last_detailed_fetch',
        'user_id',
        'status_id',
    ];

    function gameSources()
    {
        return $this->belongsToMany(GameSource::class)->withPivot(['uri', 'source_id', 'subscription_expires', 'is_subscribed'])->withTimestamps();
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

    public function predictions()
    {
        return $this->hasMany(Prediction::class, 'predicted_winner_id');
    }
}
