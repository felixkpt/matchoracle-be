<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatchesJobLog extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'task',
        'date',
        'job_run_counts',
        'competition_run_counts',
        'fetch_run_counts',
        'fetch_success_counts',
        'fetch_failed_counts',
        'last_fail_message',
        'source_id'
    ];
}
