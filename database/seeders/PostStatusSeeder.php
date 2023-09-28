<?php

namespace Database\Seeders;

use App\Models\PostStatus;
use Illuminate\Database\Seeder;

class PostStatusSeeder extends Seeder
{
    public function run()
    {
        $statusNames = [
            'draft', 'pending_review', 'scheduled', 'published',
            'private', 'trash', 'archived', 'draft_in_review', 'rejected'
        ];

        foreach ($statusNames as $name) {
            PostStatus::updateOrCreate([
                'name' => $name,
                'description' => ucfirst(str_replace('_', ' ', $name)) . ' status.',
            ]);
        }
    }
}
