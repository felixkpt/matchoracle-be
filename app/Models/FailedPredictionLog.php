<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedPredictionLog extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'message'];
}
