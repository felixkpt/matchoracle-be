<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GamePredictionType extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'status_id',
    ];
}
