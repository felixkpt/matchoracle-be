<?php

namespace Database\Seeders;

use App\Models\PostCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostCategoriesSeeder extends Seeder
{
    public function run()
    {
        $sections = [
            ['title' => 'General Usage'],
            ['title' => 'Frontend Post'],
            ['title' => 'API Post'],
            ['title' => 'Backend Post'],
        ];

        foreach ($sections as $section) {
            $slug = Str::slug($section['title']);
            PostCategory::updateOrCreate(['slug' => $slug], ['title' => $section['title'], 'slug' => $slug]);
        }
    }
}
