<?php

namespace Database\Seeders;

use App\Models\GameScoreStatus;
use Illuminate\Database\Seeder;

class GameScoreStatusSeeder extends Seeder
{
    public function run()
    {

        $statuses = [
            ['name' => 'scheduled', 'icon' => 'ic:sharp-published-with-changes', 'class' => 'text-success'],
            ['name' => 'playing', 'icon' => 'mdi:soccer', 'class' => 'text-primary'],
            ['name' => 'played', 'icon' => 'mdi:check-circle-outline', 'class' => 'text-success'],
            ['name' => 'postponed', 'icon' => 'mdi:clock-outline', 'class' => 'text-warning'],
            ['name' => 'cancelled', 'icon' => 'mdi:cancel', 'class' => 'text-danger'],
        ];

        foreach ($statuses as $status) {
            GameScoreStatus::updateOrCreate(['name' => $status['name']], [
                'name' => $status['name'],
                'description' => ucfirst(str_replace('_', ' ', $status['name'])) . ' status.',
                'icon' => $status['icon'],
                'class' => $status['class'],
            ]);
        }
    }
}
