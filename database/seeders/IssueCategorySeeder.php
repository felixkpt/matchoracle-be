<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IssueCategory;
use App\Models\User;

class IssueCategorySeeder extends Seeder
{
    public function run()
    {
        $issueCategories = [
            [
                'name' => 'Inquiry',
            ],
            [
                'name' => 'Request',
            ],
            [
                'name' => 'Complaint',
            ],
            [
                'name' => 'Non-Contact',
            ],
            [
                'name' => 'by fl',
            ],
        ];

        foreach ($issueCategories as $issueCategory) {
            IssueCategory::updateOrCreate(
                ['name' => $issueCategory['name']],
                [
                    'name' => $issueCategory['name'],
                    'user_id' => User::inRandomOrder()->first()->id,
                    // 80% 1
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                ]
            );
        }
    }
}
