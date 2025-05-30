<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Country;
use App\Models\Competition;
use App\Services\ClientHelper\Client;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class InitialCompetitionsHandler
{

    use ForebetInitializationTrait;
    protected $jobId;

    /**
     * Constructor for the CompetitionsHandler class.
     * @property string $jobId          The unique identifier for the job.
     * Initializes the strategy and calls the trait's initialization method.
     */
    public function __construct()
    {
        $this->initialize();

        if (!$this->jobId) {
            $this->jobId = str()->random(6);
        }
    }

    public function seedCompetitions()
    {
        $content = Client::get($this->sourceUrl);
        $crawler = new Crawler($content);

        // Extracted data from the HTML will be stored in this array
        $allCountries = [];
        $allInternationals = [];

        $crawler->filter('.moduletable_foremenu')->each(function ($crawler) use (&$allCountries, &$allInternationals) {

            if ($crawler->filter('.moduletable')->text() === 'Countries') {

                $countries = $this->getCountries($crawler, 'domestic');
                // Filter out null values and merge the extracted data for this country
                $allCountries = array_merge($allCountries, array_values(array_filter($countries)));
            } else if ($crawler->filter('.moduletable')->text() === 'International') {

                $countries = $this->getCountries($crawler, 'international');
                // Filter out null values and merge the extracted data for this country
                $allInternationals = array_merge($allInternationals, array_values(array_filter($countries)));
            }
        });


        // Combine data from both arrays
        $combinedData = array_merge($allCountries, $allInternationals);

        // Store the combined data in a new JSON file
        $competitions = $this->storeCompetitions("database/seeders/jsons/forebet_combined_competitions_data.json", $combinedData);


        foreach ($competitions as $competitionData) {

            $countryData = $competitionData->country;
            $country = Country::where('name', $countryData->name)->first();
            if (!$country) {
                $country = Country::create(
                    ['name' => $countryData->name, 'slug' => Str::slug($countryData->name), 'continent_id' => 0, 'has_competitions' => true]
                );
            }

            $country->has_competitions = true;
            $country->save();

            foreach ($competitionData->competitions as $competitionData) {
                $name = $competitionData->name;
                $category = $competitionData->category;
                $plan = $competitionData->plan ?? null;
                $gender = $competitionData->gender ?? null;
                // last_updated should be logged on the level of source not competitions
                $last_updated = $competitionData->lastUpdated ?? null;
                $available_seasons = $competitionData->numberOfAvailableSeasons ?? null;
                $current_season = $competitionData->currentSeason ?? null;

                $competition = Competition::updateOrCreate(
                    [
                        'name' => $name,
                        'country_id' => $country->id,
                        'gender' => $gender,
                    ],
                    [
                        'name' => $name,
                        'slug' => Str::slug($name),
                        'category' => $category,
                        'continent_id' => $country->continent_id,
                        'country_id' => $country->id,
                        'plan' => $plan,
                        'gender' => $gender,
                        'available_seasons' => $available_seasons,
                        'status_id' => inActiveStatusId(),
                    ]
                );

                // Check if the game source with the given ID doesn't exist
                if (!$competition->gameSources()->where('game_source_id', $this->sourceId)->exists()) {
                    // Attach the relationship with the URI
                    $competition->gameSources()->attach($this->sourceId, ['source_uri' => $competitionData->uri, 'is_subscribed' => true]);
                }

                // Save/update current season
                $seasonData = $current_season;
                if ($seasonData) {
                    (new SeasonsHandler())->updateOrCreate($seasonData, $country, $competition, true);
                }
            }
        }
    }

    private function getCountries($crawler, $category)
    {

        // Extracted data for a single country will be stored in this array
        $countries = $crawler->filter('.tree_foremenu ul li')->each(function ($countryData) use ($category) {
            $country = $countryData->filter('.mainlevel_foremenu');

            // Check if the selector matches any elements
            if ($country->count() == 1) {
                $country = trim($country->text());
                $country = preg_replace('#^N\. Ireland$#ui', 'Northern Ireland', $country);
                $country = preg_replace('#^Türkiye$#ui', 'Turkey', $country);
                $country = preg_replace('#^Bosnia$#ui', 'Bosnia and Herzegovina', $country);
                $country = preg_replace('#^UAE$#ui', 'United Arab Emirates', $country);

                $competitions = $countryData->filter('ul.mm-listview li a.sublevel_foremenu')->each(function ($competitionData) use ($category) {

                    $competition = $competitionData->text();
                    $uri = $competitionData->attr('href');

                    return ['uri' => $uri, 'name' => $competition, 'category' => $category, 'type' => null, 'gender' => Str::contains($competition, 'Women', true) ? 2 : 1];
                });

                return [
                    'country' => ['name' => $country],
                    'competitions' => $competitions
                ];
            }

            return null;
        });

        return $countries;
    }

    private function storeCompetitions($path, $content)
    {
        // Store the contents in a JSON file
        $filename = base_path($path);
        $jsonContent = json_encode($content, JSON_PRETTY_PRINT);

        // Save the JSON content to the file
        file_put_contents($filename, $jsonContent);

        $handle = fopen($filename, "rb");
        $contents = fread($handle, filesize($filename));
        $competitions = json_decode($contents);
        fclose($handle);

        return $competitions;
    }
}
