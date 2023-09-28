<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use App\Models\PostTopic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostTopicsSeeder extends Seeder
{
    public function run()
    {
        $topics = [
            ['category_id' => PostCategory::inRandomOrder()->first()->id, 'title' => 'Introduction'],
            ['category_id' => PostCategory::inRandomOrder()->first()->id, 'title' => 'Components'],
            ['category_id' => PostCategory::inRandomOrder()->first()->id, 'title' => 'Database Schema'],
        ];

        foreach ($topics as $topic) {
            $slug = Str::slug($topic['title']);
            PostTopic::updateOrCreate(['slug' => $slug], ['category_id' => $topic['category_id'], 'title' => $topic['title'], 'slug' => $slug]);
        }
    }
}
