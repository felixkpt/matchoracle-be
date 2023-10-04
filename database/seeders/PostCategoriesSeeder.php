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
            ['name' => 'General Usage'],
            ['name' => 'Frontend Post'],
            ['name' => 'API Post'],
            ['name' => 'Backend Post'],
        ];

        foreach ($sections as $section) {
            $slug = Str::slug($section['name']);
            PostCategory::updateOrCreate(['slug' => $slug], ['name' => $section['name'], 'slug' => $slug]);
        }
    }
}
