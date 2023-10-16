<?php

namespace App\Models;

use App\Traits\ExcludeSystemFillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSource extends Model
{
    use HasFactory, HasUlids, CommonModelRelationShips, ExcludeSystemFillable;

    protected $fillable = [
        'name',
        'url',
        'description',
        'user_id',
        'status_id',
        'priority_number',
    ];

    protected $systemFillable = [
        'user_id',
        'status_id',
    ];
}
