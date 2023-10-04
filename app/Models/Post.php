<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = ['title', 'slug', 'content_short', 'content', 'image', 'category_id', 'topic_id', 'status_id', 'user_id', 'priority_number'];

    function user()
    {
        return $this->belongsTo(User::class);
    }
    
    function status()
    {
        return $this->belongsTo(PostStatus::class);
    }

    function category()
    {
        return $this->belongsTo(PostCategory::class);
    }

    function topic()
    {
        return $this->belongsTo(PostTopic::class);
    }
}
