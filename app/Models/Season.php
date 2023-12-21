<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'start_date',
        'end_date',
        'current_matchday',
        'total_matchdays',
        'played_matches',
        'competition_id',
        'is_current',
        'winner_id',
    ];

    protected $casts = [
        'stages' => 'json',
    ];

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function standings()
    {
        return $this->hasMany(Standing::class);
    }

    public function winner()
    {
        return $this->belongsTo(Team::class);
    }
}
