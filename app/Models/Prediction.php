<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prediction extends Model
{
    use HasFactory, HasUlids, CommonModelRelationShips;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function match()
    {
        return $this->belongsTo(Game::class);
    }

    public function predictedWinner()
    {
        return $this->belongsTo(Team::class, 'predicted_winner_id');
    }
}
