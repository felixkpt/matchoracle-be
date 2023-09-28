<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamsLog extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'team_id',
        'fetch_counts',
        'fetch_details',
        'detailed_fetch_counts',
        'detailed_fetch_details',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(fn ($model) => defaultColumns($model));
    }
}
