<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'dial_code',
        'continent_id',
        'is_international',
        'priority_number',
        'has_competitions',
        'flag',
        'user_id',
        'status_id',
    ];

    function competitions()
    {
        return $this->hasMany(Competition::class)->orderby('priority_number');
    }

    function continent()
    {
        return $this->belongsTo(Continent::class);
    }

    // Getter for Logo attribute
    public function getLogoAttribute()
    {
        return asset("images/countries/{$this->flag}");
    }
}
