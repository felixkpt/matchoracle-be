<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionStatisticJobLog extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'date',

        'job_run_counts',
        'competition_run_counts',
        'action_run_counts',
        'average_seconds_per_action_run',
        'created_counts',
        'updated_counts',
        'failed_counts',
    ];
}
