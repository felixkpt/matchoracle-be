<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PredictionJobLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'prediction_type_id',
        'date',
        'job_run_counts',
        'competition_run_counts',
        'prediction_success_counts',
        'prediction_failed_counts',
        'average_minutes_per_run',
    ];
}
