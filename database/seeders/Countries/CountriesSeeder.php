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

        // Country::truncate();
        // DB::statement('ALTER TABLE countries AUTO_INCREMENT = 1100;');

        $countries =  json_decode(file_get_contents(database_path('seeders/jsons/countries.json')), true);

        // dd(count($countries))
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
                ['name' => $country['name']],
                [
                    'name' => $country['name'],
                    'slug' => Str::slug($country['name']),
                    'code' => $country['code'],
                    'dial_code' => $country['dial_code'],
                    'flag' => 'images/flags/png100px/' . Str::slug($country['code']) . '.png',
                    'is_international' => $is_international,
                    'continent_id' => $continent->id ?? 0,
                    'status_id' => activeStatusId()
                ]
            );
        }
    }
}
