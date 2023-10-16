<?php

namespace App\Models;

use App\Traits\ExcludeSystemFillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Continent extends Model
{
    use HasFactory, HasUlids, CommonModelRelationShips, ExcludeSystemFillable;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'image',
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
