<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, CommonModelRelationShips;

    protected $fillable = ['title', 'slug', 'content_short', 'content', 'image', 'category_id', 'topic_id', 'status_id', 'user_id', 'position'];

    function category()
    {
        return $this->belongsTo(PostCategory::class);
    }

    function topic()
    {
        return $this->belongsTo(PostTopic::class);
    }

    function status()
    {
        return $this->belongsTo(PostStatus::class);
    }
}
