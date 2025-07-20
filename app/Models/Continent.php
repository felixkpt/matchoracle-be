<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Continent extends Model
{
    use HasFactory, CommonModelRelationShips, ExcludeSystemFillable;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'flag',
        'priority_number',
        'user_id',
        'status_id',
    ];

    protected $systemFillable = [
        'user_id',
        'status_id',
    ];

    function competitions()
    {
        return $this->hasMany(Competition::class);
    }

}
