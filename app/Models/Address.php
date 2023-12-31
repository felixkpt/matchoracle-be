<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'user_id',
        'status_id',
    ];
}
