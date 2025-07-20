<?php

namespace App\Services\GameSources\FootballData;

use App\Models\Competition;
use App\Models\Country;
use App\Repositories\FootballData;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FootballDataInit
{
    public $api;
    public $competitions;
    public $seasons;
    public $standings;
    public $teams;
    public $matches;

    public function __construct()
    {
        $this->api = new FootballData();
        $this->competitions = new CompetitionsHandler($this->api);
        $this->seasons = new Seasons($this->api);
        $this->standings = new Standings($this->api);
        $this->teams = new Teams($this->api);
        $this->matches = new Matches($this->api);
    }

    public function initialCompetitions()
    {
        $filename = base_path("database/seeders/jsons/football_data_competitions.json");

        $handle = fopen($filename, "rb");
        $contents = fread($handle, filesize($filename));
        $competitions = json_decode($contents)->competitions;
        fclose($handle);

        foreach ($competitions as $competitionData) {

            $countryData = $competitionData->area;
            $country = Country::where('name', $countryData->name)->first();
            if (!$country) {
                $country = Country::create(
                    ['name' => $countryData->name, 'slug' => Str::slug($countryData->name), 'continent_id' => 0, 'has_competitions' => true]
                );
            }

            $country->has_competitions = true;
            $country->save();

            $name = $competitionData->name;
            $code = $competitionData->code;
            $type = $competitionData->type;
            $logo = $competitionData->logo ?? $competitionData->emblem;
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
                    'code' => $code,
                    'type' => $type,
                    'logo' => $logo,
                    'continent_id' => $country->continent_id,
                    'country_id' => $country->id,
                    'plan' => $plan,
                    'last_updated' => Carbon::parse($last_updated)->format('Y-m-d H:i:s'),
                    'available_seasons' => $available_seasons,
                ]
            );

            // Check if the game source with the given ID doesn't exist
            if (!$competition->gameSources()->where('game_source_id', $this->api->sourceId)->exists()) {
                // Attach the relationship with the URI
                $competition->gameSources()->attach($this->api->sourceId, ['source_id' => $competitionData->id]);
            }

            // Save/update current season
            $seasonData = $current_season;
            if ($seasonData) {
                $this->seasons()->updateOrCreate($seasonData, $country, $competition, true);
            }
        }
    }

    public function competitions()
    {
        return new CompetitionsHandler($this->api);
    }

    public function seasons()
    {
        return new Seasons($this->api);
    }

    public function standings()
    {
        return new Standings($this->api);
    }

    public function teams()
    {
        return new Teams($this->api);
    }

    public function matches()
    {
        return new Matches($this->api);
    }
}
