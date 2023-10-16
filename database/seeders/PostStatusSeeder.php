<?php

namespace Database\Seeders;

use App\Models\PostStatus;
use Illuminate\Database\Seeder;

class PostStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'published', 'icon' => 'ic:sharp-published-with-changes', 'class' => 'text-success'],
            ['name' => 'pending_review', 'icon' => 'mdi:receipt-text-pending', 'class' => 'text-primary'],
            ['name' => 'scheduled', 'icon' => 'mdi:scheduled-payment', 'class' => 'text-info'],
            ['name' => 'private', 'icon' => 'ri:git-repository-private-line', 'class' => 'text-secondary'],
            ['name' => 'archived', 'icon' => 'bi:archive', 'class' => 'text-dark'],
            ['name' => 'draft_in_review', 'icon' => 'carbon:result-draft', 'class' => 'text-warning'],
            ['name' => 'draft', 'icon' => 'carbon:result-draft', 'class' => 'text-warning'],
            ['name' => 'rejected', 'icon' => 'icon-park-outline:reject', 'class' => 'text-danger'],
            ['name' => 'trash', 'icon' => 'fe:trash', 'class' => 'text-danger'],
        ];

        foreach ($statuses as $status) {
            PostStatus::updateOrCreate([
                'name' => $status['name'],
                'description' => ucfirst(str_replace('_', ' ', $status['name'])) . ' status.',
                'icon' => $status['icon'],
                'class' => $status['class'],
            ]);
        }
    }
}
