<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamsLog extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'team_id',
        'fetch_counts',
        'fetch_details',
        'detailed_fetch_counts',
        'detailed_fetch_details',
    ];

}
