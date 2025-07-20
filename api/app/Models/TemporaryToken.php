<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemporaryToken extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'token',
        'expires_at'
    ];
}
