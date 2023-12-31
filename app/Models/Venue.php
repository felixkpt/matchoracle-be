<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'priority_number',
        'user_id',
        'status_id',
    ];
}
