<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Standing extends Model
{
    use HasFactory, CommonModelRelationShips;

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

    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    public function standingTable()
    {
        return $this->hasMany(StandingTable::class, 'standing_id')->orderby('position');
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
