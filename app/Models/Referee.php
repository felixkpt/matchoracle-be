<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referee extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'type',
        'country_id',
        'status_id',
        'user_id',
    ];
}
