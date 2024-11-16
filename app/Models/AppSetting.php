<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = [
        'category',
        'name',
        'value',
        'description',
        'user_id',
        'status_id',
    ];
}
