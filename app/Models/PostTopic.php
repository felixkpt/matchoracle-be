<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTopic extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'image', 'status_id', 'user_id', 'position'];
}
