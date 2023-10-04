<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            [
                'name' => 'active',
                'icon' => 'ontisto:checkbox-active'
            ],
            [
                'name' => 'in_active',
                'icon' => 'material-symbols:inactive-order-rounded'
            ],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate([
                'name' => $status['name'],
                'description' => ucfirst(str_replace('_', ' ', $status['name'])) . ' status.',
                'icon' => $status['icon'],
            ]);
        }
    }
}
