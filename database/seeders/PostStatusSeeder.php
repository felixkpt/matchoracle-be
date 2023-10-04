<?php

namespace Database\Seeders;

use App\Models\PostStatus;
use Illuminate\Database\Seeder;

class PostStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'draft', 'icon' => 'ontisto:checkbox-active'],
            ['name' => 'pending_review', 'icon' => 'mdi:receipt-text-pending'],
            ['name' => 'scheduled', 'icon' => 'mdi:scheduled-payment'],
            ['name' => 'published', 'icon' => 'ic:sharp-published-with-changes'],
            ['name' => 'private', 'icon' => 'ri:git-repository-private-line'],
            ['name' => 'trash', 'icon' => 'fe:trash'],
            ['name' => 'archived', 'icon' => 'bi:archive'],
            ['name' => 'draft_in_review', 'icon' => 'carbon:result-draft'],
            ['name' => 'rejected', 'icon' => 'icon-park-outline:reject'],
        ];

        foreach ($statuses as $status) {
            PostStatus::updateOrCreate([
                'name' => $status['name'],
                'description' => ucfirst(str_replace('_', ' ', $status['name'])) . ' status.',
                'icon' => $status['icon'],
            ]);
        }
    }
}
