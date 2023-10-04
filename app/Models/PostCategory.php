<?php

namespace App\Models;

use App\Traits\ExcludeSystemFillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    use HasFactory, ExcludeSystemFillable, HasUlids;
    protected $fillable = ['name', 'slug', 'description', 'image', 'status_id', 'user_id', 'parent_category_id', 'priority_number'];
    protected $systemFillable = ['parent_category_id'];
}
