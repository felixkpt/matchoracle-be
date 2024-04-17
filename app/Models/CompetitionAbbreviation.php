<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionAbbreviation extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'is_international',
        'country_id',
        'competition_id',
        'status_id',
        'user_id',
    ];

    function country()
    {
        return $this->belongsTo(Country::class);
    }
    
    function competition()
    {
        return $this->belongsTo(Competition::class);
    }
}
