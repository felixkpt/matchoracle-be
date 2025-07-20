<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stadium extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'location',
        'team_id',
        'user_id',
        'status_id',
    ];
}
