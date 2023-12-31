<?php

namespace App\Models;

use App\Traits\ExcludeSystemFillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    use HasFactory, CommonModelRelationShips, ExcludeSystemFillable;
   
    protected $fillable = ['parent_category_id', 'name', 'slug', 'description', 'image', 'status_id', 'user_id', 'priority_number'];
    protected $systemFillable = [];

    function category()
    {
        return $this->belongsTo(self::class, 'parent_category_id');
    }
}
