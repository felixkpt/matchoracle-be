<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Season extends Model
{
    use HasFactory, HasUlids, CommonModelRelationShips;

    protected $fillable = [
        'start_date',
        'end_date',
        'current_matchday',
        'total_matchdays',
        'played',
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

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }
}
