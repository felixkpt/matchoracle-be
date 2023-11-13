<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Competition;
use App\Models\Country;
use App\Services\GameSources\FootballDataStrategy;
use App\Services\GameSources\GameSourceStrategy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CompetitionSeeder extends Seeder
{
    protected $sourceContext;

    function __construct(protected Competition $model)
    {
        // Instantiate the context class
        $this->sourceContext = new GameSourceStrategy();
        
        // Set the desired game source (can switch between sources dynamically)
        $this->sourceContext->setGameSourceStrategy(new FootballDataStrategy());

    }

    public function run()
    {

        $filename = base_path("database/seeders/competitions.json");

        $handle = fopen($filename, "rb");
        $contents = fread($handle, filesize($filename));
        $competitions = json_decode($contents)->competitions;
        fclose($handle);

        foreach ($competitions as $competitionData) {

            $country = $competitionData->area;
            $country = Country::where('name', $country->name)->first();
            $country->has_competitions = true;
            $country->save();

            $name = $competitionData->name;
            $code = $competitionData->code;
            $type = $competitionData->type;
            $emblem = $competitionData->emblem;
            $plan = $competitionData->plan;
            $last_updated = $competitionData->lastUpdated;
            $available_seasons = $competitionData->numberOfAvailableSeasons;
            $current_season = $competitionData->currentSeason;

            $competition = Competition::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'code' => $code,
                'type' => $type,
                'emblem' => $emblem,
                'continent_id' => $country->continent_id,
                'country_id' => $country->id,
                'plan' => $plan,
                'last_updated' => Carbon::parse($last_updated)->format('Y-m-d H:i:s'),
                'available_seasons' => $available_seasons,
            ]);

            // Check if the game source with the given ID doesn't exist
            if (!$competition->gameSources()->where('game_source_id', $this->sourceContext->getId())->exists()) {
                // Attach the relationship with the URI
                $competition->gameSources()->attach($this->sourceContext->getId(), ['source_id' => $competitionData->id]);
            }

            // Save/update current season
            $seasonData = $current_season;
            if ($seasonData) {
                $this->sourceContext->seasons()->updateOrCreate($seasonData, $country, $competition, true);
            }
        }
    }
}
