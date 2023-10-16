<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LaracraftTech\LaravelDynamicModel\DynamicModel;

// class Game extends Model implements DynamicModelInterface (this would also work)
class Game extends DynamicModel
{
    use HasFactory, HasUlids, CommonModelRelationShips;

    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function team1()
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2()
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function predictions()
    {
        return $this->hasMany(Prediction::class);
    }
}
