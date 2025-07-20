<?php

namespace App\Services\GameSources\FootballData;

use App\Models\Address;
use App\Models\Country;
use App\Models\Coach;
use App\Models\Team;
use App\Models\Venue;
use App\Repositories\FootballData;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Teams
{
    public $api;

    public function __construct()
    {
        $this->api = new FootballData();
    }

    function findTeamById($id)
    {
        $teamData = $this->api->findTeamById($id);

        $country = $teamData->area;
        $country = Country::updateOrCreate(
            [
                'name' => $country->name,
                'code' => $country->code,
            ],
            [
                'name' => $country->name,
                'slug' => Str::slug($country->name),
                'code' => $country->code,
                'flag' => $country->flag,
            ]
        );

        $this->updateOrCreate($teamData, $country);
    }

    function updateOrCreate($teamData, $country, $competition = null)
    {

        $name = $teamData->name;
        // Create or update the team record

        $address = null;
        if (isset($teamData->address))
            $address = Address::updateOrCreate(
                [
                    'name' => $teamData->address,
                ],
                [
                    'name' => $teamData->address,
                ]
            );

        $venue = null;
        if (isset($teamData->venue))
            $venue = Venue::updateOrCreate(
                [
                    'name' => $teamData->venue,
                ],
                [
                    'name' => $teamData->venue,
                    'slug' => Str::slug($teamData->venue),
                ]
            );

        $name = $teamData->name;

        $arr = [
            'name' => $name,
            'slug' => Str::slug($name),
            'short_name' => $teamData->shortName,
            'tla' => $teamData->tla,
            'country_id' => $country->id,
            'logo' => $teamData->crest,
        ];

        if (isset($address)) {
            $arr['address_id'] = $address->id;
        }

        if (isset($venue)) {
            $arr['venue_id'] = $venue->id;
        }

        if (isset($competition) && $competition->type == 'LEAGUE') {
            $arr['competition_id'] = $competition->id;
        }

        if (isset($teamData->website)) {
            $arr['website'] = $teamData->website;
        }

        if (isset($teamData->founded)) {
            $arr['founded'] = $teamData->founded;
        }

        if (isset($teamData->club_colors)) {
            $arr['club_colors'] = $teamData->club_colors;
        }

        if (isset($teamData->lastUpdated)) {
            $arr['last_updated'] = Carbon::parse($teamData->lastUpdated)->format('Y-m-d H:i:s');
        }

        $team = Team::updateOrCreate(
            [
                'name' => $name,
                'short_name' => $teamData->shortName,
                'tla' => $teamData->tla,
                'country_id' => $country->id,
            ],
            $arr
        );

        $this->saveCoach($teamData, $team);

        // Check if the game source with the given ID doesn't exist
        if (!$team->gameSources()->where('game_source_id', $this->api->sourceId)->exists()) {
            // Attach the relationship with the URI
            $team->gameSources()->attach($this->api->sourceId, ['source_id' => $teamData->id]);
        }

        return $team;
    }

    function updateByCompetition($id)
    {
        $teams = Team::where('competition_id', $id)->get();
        dd($teams->count());
    }

    function saveCoach($teamData, $team)
    {

        if (isset($teamData->coach)) {
            $coach = $teamData->coach;
            $coach = Coach::updateOrCreate(
                [
                    'first_name' => $coach->firstName,
                    'last_name' => $coach->lastName,
                    'name' => $coach->name,
                ],
                [
                    'first_name' => $coach->firstName,
                    'last_name' => $coach->lastName,
                    'name' => $coach->name,
                    'date_of_birth' => $coach->dateOfBirth,
                    'nationality' => $coach->nationality,
                ]
            );

            $team->coach_id = $coach->id;
            $team->save();
        }

        return true;
    }
}
