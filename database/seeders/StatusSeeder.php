<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'active', 'icon' => 'ic:sharp-published-with-changes', 'class' => 'text-success'],
            ['name' => 'in_active', 'icon' => 'fe:disabled', 'class' => 'text-danger'],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(['name' => $status['name']], [
                'name' => $status['name'],
                'description' => ucfirst(str_replace('_', ' ', $status['name'])) . ' status.',
                'icon' => $status['icon'],
                'class' => $status['class'],
            ]);
        }
    }
}
