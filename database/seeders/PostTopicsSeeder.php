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
            ['category_id' => PostCategory::inRandomOrder()->first()->id, 'name' => 'Introduction'],
            ['category_id' => PostCategory::inRandomOrder()->first()->id, 'name' => 'Components'],
            ['category_id' => PostCategory::inRandomOrder()->first()->id, 'name' => 'Database Schema'],
        ];

        foreach ($topics as $topic) {
            $slug = Str::slug($topic['name']);
            PostTopic::updateOrCreate(['slug' => $slug], ['category_id' => $topic['category_id'], 'name' => $topic['name'], 'slug' => $slug]);
        }
    }
}
