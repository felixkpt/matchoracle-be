<?php

namespace App\Models;

use App\Traits\ExcludeSystemFillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    use HasFactory, CommonModelRelationShips, ExcludeSystemFillable;

    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'date_of_birth',
        'nationality_id',
    ];

    protected $systemFillable = [
        'name',
    ];

    function nationality()
    {
        return $this->belongsTo(Country::class);
    }
}
