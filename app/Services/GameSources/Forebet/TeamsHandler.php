<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Address;
use App\Models\Country;
use App\Models\Coach;
use App\Models\Team;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TeamsHandler
{
    use ForebetInitializationTrait;

    protected $has_errors = false;
    /**
     * Constructor for the CompetitionsHandler class.
     * 
     * Initializes the strategy and calls the trait's initialization method.
     */
    public function __construct()
    {
        $this->initialize();
    }

    function findTeamById($id)
    {
        $teamData = $this->findTeamById($id);

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

    function updateOrCreate($teamData, $country, $competition = null, $season = null, $ignore_competition = false)
    {

        if (!isset($teamData['name'])) return false;

        $name = $teamData['name'];
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

        $country_id = (isset($country->id) && $country->continent->id != 'World') ? $country->id : null;
        if (!$country_id) return null;

        $arr = [
            'name' => $name,
            'slug' => Str::slug($name),
            'country_id' => $country_id,
            'gender' => $competition->gender,
        ];

        if (isset($teamData['logo'])) {
            $arr['logo'] = $teamData['logo'];
        }

        if (isset($address)) {
            $arr['address_id'] = $address->id;
        }

        if (isset($venue)) {
            $arr['venue_id'] = $venue->id;
        }

        if (!$ignore_competition && isset($competition) && $competition->type == 'LEAGUE' && isset($season) && $season->is_current) {
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
                'country_id' => $country_id,
                'gender' => $competition->gender,
            ],
            $arr
        );

        static::saveCoach($teamData, $team);

        // Check if the game source with the given ID doesn't exist
        if (!$team->gameSources()->where('game_source_id', $this->sourceId)->exists()) {
            // Attach the relationship with the URI
            $team->gameSources()->attach($this->sourceId, ['source_uri' => $teamData['uri']]);
        }

        return $team;
    }

    function updateByCompetition($id)
    {
        $teams = Team::where('competition_id', $id)->get();
        dd($teams->count());
    }

    static function saveCoach($teamData, $team)
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
