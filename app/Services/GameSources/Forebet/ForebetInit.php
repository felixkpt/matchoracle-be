<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Competition;
use App\Models\Country;
use App\Services\Client;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class ForebetInit extends BaseHandlerController
{
    public $competitionsHandler;
    public $seasonsHandler;
    public $standingsHandler;
    public $teamsHandler;
    public $matchesHandler;

    public function __construct()
    {
        parent::__construct();

        $this->competitionsHandler = new CompetitionsHandler();
        $this->seasonsHandler = new SeasonsHandler();
        $this->standingsHandler = new StandingsHandler();
        $this->matchesHandler = new MatchesHandler();
    }

    public function initialCompetitions()
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
                $last_updated = $competitionData->lastUpdated ?? null;
                $available_seasons = $competitionData->numberOfAvailableSeasons ?? null;
                $current_season = $competitionData->currentSeason ?? null;

                $competition = Competition::updateOrCreate(
                    [
                        'name' => $name,
                        'country_id' => $country->id,
                    ],
                    [
                        'name' => $name,
                        'slug' => Str::slug($name),
                        'category' => $category,
                        'continent_id' => $country->continent_id,
                        'country_id' => $country->id,
                        'plan' => $plan,
                        'last_updated' => Carbon::parse($last_updated)->format('Y-m-d H:i:s'),
                        'available_seasons' => $available_seasons,
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
                    $this->seasons()->updateOrCreate($seasonData, $country, $competition, true);
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
                $country = $country->text();

                $competitions = $countryData->filter('ul.mm-listview li a.sublevel_foremenu')->each(function ($competitionData) use ($category) {

                    $competition = $competitionData->text();
                    $uri = $competitionData->attr('href');

                    return ['uri' => $uri, 'name' => $competition, 'category' => $category, 'type' => null];
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
