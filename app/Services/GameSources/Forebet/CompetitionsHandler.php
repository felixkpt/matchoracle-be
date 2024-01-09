<?php

namespace App\Services\GameSources\Forebet;

use App\Models\Address;
use App\Models\Country;
use App\Models\Coach;
use App\Models\CoachContract;
use App\Models\Competition;
use App\Models\Team;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompetitionsHandler
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

    function updateOrCreate($data)
    {

        $competitionIds = [
            // englandEPL
            2021,
            // Championship
            2016,
            // primera division 
            2014,
            // italy 0
            2124,
            // Eredivisie  0
            2103,
            // Primeira Liga 
            2017,
            // Campeonato Brasileiro Série B 0
            2029,
            // Campeonato Brasileiro Série A
            2013,
            // Copa Libertadores 
            2152,
            // European Championship 0
            2018,
            // UEFA Champions League 
            2001,

        ];

        if (!$data['source_id']) {
            return response(['type' => 'warning', 'message' => 'Could not save competition.'], 500);
        }

        $competitionData = $this->findCompetitionById($data['source_id']);

        // $jsonResult = json_encode($standing, JSON_PRETTY_PRINT);
        // dd($jsonResult);
        // dd($competitionData);
        // die;


        $country = $competitionData->area;

        $country = Country::where('name', $country->name)->first();
        $country->has_competitions = true;
        $country->save();

        $name = $competitionData->name;

        $arr = [
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => $competitionData->code,
            'type' => $competitionData->type,
            'country_id' => $country->id,
            'plan' => $competitionData->plan ?? null,
            'last_updated' => Carbon::parse($competitionData->lastUpdated)->format('Y-m-d H:i:s'),
        ];

        if ($competitionData->emblem) {
            $arr['logo'] = $competitionData->emblem;
        }

        $competition = Competition::updateOrCreate(
            [
                'name' => $name,
                'code' => $competitionData->code,
                'country_id' => $country->id,
            ],
            $arr
        );


        // Check if $item (URI && source_id) is not null before proceeding
        if ($data['uri'] || $data['source_id']) {
            // Check if the game source with the given ID doesn't exist
            if (!$competition->gameSources()->where('game_source_id', $this->sourceId)->exists()) {
                // Attach the relationship with the URI & or source_id
                $competition->gameSources()->attach($this->sourceId, $data);
            } else {
                $competition->gameSources()->where('game_source_id', $this->sourceId)->update($data);
            }
        } else {
            // Detach the relationship if URI & source_id are null
            $competition->gameSources()->detach($this->sourceId);
        }

        // Save all seasons
        $seasons = $competitionData->seasons;
        foreach ($seasons as $seasonData) {
            SeasonsHandler::updateOrCreate($seasonData, $country, $competition, false);
        }

        // Save/update current season
        $seasonData = $competitionData->currentSeason;
        if ($seasonData) {
            SeasonsHandler::updateOrCreate($seasonData, $country, $competition, true);
        }

        return response(['message' => 'Successfully saved competition.']);
    }

    

    

    /**
     * @param int $id
     * @param int $season
     * @param int $matchday
     */
    function fetchMatches($id, $season = null, $matchday = null)
    {
        Log::alert('Match day::', [$id, $matchday]);

        $competition = Competition::whereHas('gameSources', function ($q) use ($id) {
            $q->where('competition_id', $id);
        })->first();

        if (!$competition) {
            return response(['message' => 'Competition #' . $id . ' not found.'], 404);
        }

        // Access the source_id value for the pivot
        $source = $competition->gameSources->first()->pivot;
        if (!$source) {
            return response(['message' => 'Source for competition #' . $id . ' not found.'], 404);
        }

        if (!$source->is_subscribed) {
            return response(['message' => 'Source #' . $source->source_id . ' not subscribed.'], 402);
        }

        $matches = $this->findMatchesByCompetition($source->source_id, $season, $matchday);

        Log::critical('MATCHES', [$matches]);

        return response(['message' => 'Matches for ' . $competition->name . ' updated.']);
    }

    function show()
    {
        $competition = Competition::with(['area', 'currentSeason', 'seasons', 'stages'])->first();

        // Format the data as needed
        $result = [
            'area' => $competition->area,
            'id' => $competition->id,
            'name' => $competition->name,
            'code' => $competition->code,
            'type' => $competition->type,
            'logo' => $competition->emblem,
            'season' => $competition->currentSeason,
            'lastUpdated' => $competition->lastUpdated,
        ];

        // Convert to JSON
        $jsonResult = json_encode($result, JSON_PRETTY_PRINT);

        // Return or echo the JSON result
        dd($result);
    }

    function findTeamById($id)
    {
        $team = $this->findTeamById($id);
        $sourceTeam = $team;

        $country = $team->country;
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
                'has_competitions' => true
            ]
        );

        $address = $team->address;
        $address = Address::updateOrCreate(
            [
                'name' => $address,
            ],
            [
                'name' => $address,
            ]
        );

        $venue = $team->venue;
        $venue = Venue::updateOrCreate(
            [
                'name' => $venue,
            ],
            [
                'name' => $venue,
            ]
        );

        $coach = $team->coach;
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

        $teamData = $team;

        $name = $teamData->name;

        $team = Team::updateOrCreate(
            [
                'name' => $name,
                'short_name' => $teamData->shortName,
                'tla' => $teamData->tla,
                'country_id' => $country->id,
            ],
            [
                'name' => $name,
                'slug' => Str::slug($name),
                'short_name' => $teamData->shortName,
                'tla' => $teamData->tla,
                'country_id' => $country->id,
                'logo' => $teamData->logo,
                'address_id' => $address->id,
                'website' => $team->website,
                'founded' => $team->founded,
                'club_colors' => $team->clubColors,
                'venue_id' => $venue->id,
                'coach_id' => $coach->id,
            ]
        );

        if ($coach) {
            $contract = $sourceTeam->coach->contract;
            $start = Carbon::parse($contract->start)->format('Y-m-d');
            $until = Carbon::parse($contract->until)->format('Y-m-d');
            $contract = CoachContract::updateOrCreate(
                [
                    'team_id' => $team->id,
                    'coach_id' => $coach->id,
                    'start' => $start,
                    'until' => $until,
                ],
                [
                    'team_id' => $team->id,
                    'coach_id' => $coach->id,
                    'start' => $start,
                    'until' => $until,
                ]
            );
            $coach->contract_id = $contract->id;
            $coach->save();
        }

        // Check if the game source with the given ID doesn't exist
        if (!$team->gameSources()->where('game_source_id', $this->sourceId)->exists()) {
            // Attach the relationship with the URI
            $team->gameSources()->attach($this->sourceId, ['source_id' => $teamData->id]);
        }

        dd($team);

        $jsonResult = json_encode($team, JSON_PRETTY_PRINT);
        echo ($jsonResult);
    }

    function updateOrCreateTeam($teamData, $country)
    {

        $name = $teamData->name;
        // Create or update the team record
        $address = $teamData->address;
        $address = Address::updateOrCreate(
            [
                'name' => $address,
            ],
            [
                'name' => $address,
            ]
        );

        $venue = $teamData->venue;
        $venue = Venue::updateOrCreate(
            [
                'name' => $venue,
            ],
            [
                'name' => $venue,
            ]
        );

        $name = $teamData->name;

        $team = Team::updateOrCreate(
            [
                'name' => $name,
                'short_name' => $teamData->shortName,
                'tla' => $teamData->tla,
                'country_id' => $country->id,
            ],
            [
                'name' => $name,
                'slug' => Str::slug($name),
                'short_name' => $teamData->shortName,
                'tla' => $teamData->tla,
                'country_id' => $country->id,
                'logo' => $teamData->logo,
                'address_id' => $address->id,
                'website' => $teamData->website,
                'founded' => $teamData->founded,
                'club_colors' => $teamData->clubColors,
                'venue_id' => $venue->id,
                'last_updated' => Carbon::parse($teamData->lastUpdated)->format('Y-m-d H:i:s'),
            ]
        );

        $this->saveTeamCoach($teamData, $team);

        // Check if the game source with the given ID doesn't exist
        if (!$team->gameSources()->where('game_source_id', $this->sourceId)->exists()) {
            // Attach the relationship with the URI
            $team->gameSources()->attach($this->sourceId, ['source_id' => $teamData->id]);
        }

        return $team;
    }

    function saveTeamCoach($teamData, $team)
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
