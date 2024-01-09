<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionStatisticJobLog extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'date', 'job_run_counts', 'competition_run_counts', 'seasons_run_counts', 'games_run_counts',
    ];
}
