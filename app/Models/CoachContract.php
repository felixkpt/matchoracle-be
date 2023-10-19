<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoachContract extends Model
{
    use HasFactory, HasUlids, CommonModelRelationShips;

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
}
