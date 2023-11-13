<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherCondition extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'source_img',
        'user_id',
        'status_id',
    ];
}
