<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachContract extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'team_id',
        'coach_id',
        'start',
        'until',
        'user_id',
        'status_id',
    ];

    function coach()
    {
        return $this->belongsTo(Coach::class);
    }

    function team()
    {
        return $this->belongsTo(Team::class);
    }
}
