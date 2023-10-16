<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    use HasFactory, HasUlids, CommonModelRelationShips;

    protected $fillable = [
        'stage',
        'type',
        'group',
        'season_id',
        'competition_id',
    ];


    public function competition()
    {
        return $this->belongsTo(Competition::class);
    }

    public function standingTable()
    {
        return $this->hasMany(StandingTable::class, 'standing_id');
    }

    protected static function booted()
    {
        static::addGlobalScope('season_id', function (Builder $builder) {
            if (request()->season_id) {
                $builder->where('season_id', request()->season_id);
            }
        });
    }
}
