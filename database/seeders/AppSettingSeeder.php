<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingSeeder extends Seeder
{
    public function run()
    {

        $settings = [
            [
                'category' => 'general',
                'name' => 'history_start_date',
                'value' => '2018-01-01',
            ],
            [
                'category' => 'automation',
                'name' => 'predictor_url',
                'value' => 'http://127.0.0.1:8085',
            ],
            [
                'category' => 'automation',
                'name' => 'delay_competitions',
                'value' => '20',
            ],
            [
                'category' => 'automation',
                'name' => 'delay_seasons',
                'value' => '20',
            ],
            [
                'category' => 'automation',
                'name' => 'delay_games',
                'value' => '20',
            ],
        ];

        foreach ($settings as $setting) {
            AppSetting::updateOrCreate(['name' => $setting['name']], [
                'name' => $setting['name'],
                'value' => $setting['value'],
                'description' => ucfirst(str_replace('_', ' ', $setting['name'])) . ' setting.',
                'status_id' => activeStatusId(),
            ]);
        }
    }
}
