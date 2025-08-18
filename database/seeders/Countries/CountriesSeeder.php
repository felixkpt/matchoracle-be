<?php

namespace Database\Seeders\Countries;

use App\Models\Continent;
use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $countries =  json_decode(file_get_contents(database_path('seeders/jsons/countries.json')), true);

        foreach ($countries as $country) {

            if ($country['continent']) {

                $continent = Continent::updateOrCreate(
                    [
                        'name' => $country['continent']
                    ],
                    [
                        'name' => $country['continent'],
                        'slug' => Str::slug($country['continent']),
                        'status_id' => activeStatusId()
                    ]
                );
            }

            $is_international = $country['name'] == $country['continent'];

            Country::updateOrCreate(
                [
                    'name' => $country['name'],
                    'code' => $country['code'],
                ],
                [
                    'name' => $country['name'],
                    'slug' => Str::slug($country['name']),
                    'code' => $country['code'],
                    'dial_code' => $country['dial_code'],
                    'flag' => 'images/flags/svgs/' . Str::slug($country['code']) . '.svg',
                    'is_international' => $is_international,
                    'continent_id' => $continent->id ?? 0,
                    'status_id' => activeStatusId()
                ]
            );
        }
    }
}
