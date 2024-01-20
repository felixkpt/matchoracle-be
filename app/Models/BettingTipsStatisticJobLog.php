<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BettingTipsStatisticJobLog extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'type', 'range', 'date', 'job_run_counts', 'games_run_counts', 'types_run_counts'
    ];
}
