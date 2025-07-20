<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentCompetitionDetailedFetch extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'year',
        'competition_id',
        'fetched_at',
    ];
}
