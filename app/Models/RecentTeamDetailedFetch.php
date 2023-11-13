<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentTeamDetailedFetch extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'year',
        'team_id',
        'fetched_at',
    ];
}
