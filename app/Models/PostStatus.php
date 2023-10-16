<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostStatus extends Model
{
    use HasUlids, CommonModelRelationShips;

    protected $fillable = ['name', 'description', 'icon', 'class'];

    use HasFactory;
}
