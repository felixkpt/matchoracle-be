<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainPredictionJobLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'version', 'prediction_type_id', 'date', 
        'job_run_counts',
        'competition_run_counts',
        'train_success_counts', 
        'train_failed_counts',
        'average_minutes_per_run',
    ];
}
