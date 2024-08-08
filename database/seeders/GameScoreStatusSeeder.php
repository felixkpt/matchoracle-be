<?php

namespace Database\Seeders;

use App\Models\GameScoreStatus;
use Illuminate\Database\Seeder;

class GameScoreStatusSeeder extends Seeder
{
    public function run()
    {

        $statuses = [
            [
                'name' => 'Scheduled',
                'slug' => 'scheduled',
                'description' => 'Scheduled',
                'icon' => 'fontisto:checkbox-active',
                'class' => 'text-success',
            ],
            [
                'name' => 'Playing',
                'slug' => 'playing',
                'description' => 'Playing',
                'icon' => 'material-symbols:inactive-order-outline-sharp',
                'class' => 'text-danger',
            ],
            [
                'name' => 'FT results only',
                'slug' => 'ft-results-only',
                'description' => 'FT results only',
                'icon' => 'fontisto:checkbox-active',
                'class' => 'text-dark',
            ],
            [
                'name' => 'FT and HT results',
                'slug' => 'ft-and-ht-results',
                'description' => 'FT and HT results',
                'icon' => 'material-symbols:inactive-order-outline-sharp',
                'class' => 'text-muted',
            ],
            [
                'name' => 'Postponed',
                'slug' => 'postponed',
                'description' => 'Postponed',
                'icon' => 'fontisto:checkbox-active',
                'class' => 'text-dark',
            ],
            [
                'name' => 'Deactivated',
                'slug' => 'deactivated',
                'description' => 'Deactivated',
                'icon' => 'material-symbols:inactive-order-outline-sharp',
                'class' => 'text-success',
            ],
        ];

        foreach ($statuses as $status) {
            GameScoreStatus::updateOrCreate(['name' => $status['name']], [
                'name' => $status['name'],
                'slug' => $status['slug'],
                'description' => ucfirst(str_replace('_', ' ', $status['name'])) . ' status.',
                'icon' => $status['icon'],
                'class' => $status['class'],
            ]);
        }
    }
}
