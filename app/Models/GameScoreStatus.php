<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameScoreStatus extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = ['name', 'slug', 'description', 'icon', 'class'];
}
