<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompetitionAbbreviation extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'name',
        'competition_id',
    ];

}
