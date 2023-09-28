<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecentCompetitionDetailedFetch extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'year',
        'competition_id',
        'fetched_at',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => defaultColumns($model));
    }
}
