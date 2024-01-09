<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedMatchLog extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'message'];
}
