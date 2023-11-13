<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IssueSource;
use App\Models\User;

class IssueSourceSeeder extends Seeder
{

    public function run()
    {
        $issueSources = [
            [
                'name' => 'Voice Inbound',
                'icon_slug' => 'icon-phone-incoming',
            ],
            [
                'name' => 'Voice Outbound',
                'icon_slug' => 'icon-phone-outgoing',
            ],
            [
                'name' => 'Email',
                'icon_slug' => 'icon-envelop3',
            ],
            [
                'name' => 'WebChat',
                'icon_slug' => 'icon-comment-discussion',
            ],
            [
                'name' => 'Whatsapp',
                'icon_slug' => 'comment',
            ],
            [
                'name' => 'Facebook',
                'icon_slug' => 'icon-facebook2',
            ],
            [
                'name' => 'Instagram',
                'icon_slug' => 'icon-instagram',
            ],
            [
                'name' => 'Linked In',
                'icon_slug' => 'icon-linkedin2',
            ],
            [
                'name' => 'Twitter',
                'icon_slug' => 'icon-twitter',
            ],
        ];

        foreach ($issueSources as $issueSource) {
            IssueSource::updateOrCreate(
                ['name' => $issueSource['name']],
                [
                    'name' => $issueSource['name'],
                    'icon_slug' => $issueSource['icon_slug'],
                    'user_id' => User::inRandomOrder()->first()->id,
                    // 80% 1
                    'status' => rand(0, 10) <= 8 ? 1 : 0,
                ]
            );
        }
    }
}
