<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Country;
use App\Models\Game;
use App\Models\GameScore;
use App\Models\Referee;
use App\Models\Team;
use App\Repositories\FootballData;
use App\Services\GameSources\FootballData\Seasons;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FootballDataTestController extends Controller
{

    protected $api;

    function __construct()
    {
        $this->api = new FootballData();
    }

    function index()
    {

        try {

            DB::beginTransaction();

            $id = '01hd4hec19q777j2b8wwx5g0w2';

            $game = Game::whereHas('gameSources', function ($q) use ($id) {
                $q->where('game_id', $id);
            })->first();

            if (!$game) {
                return response(['message' => 'Game #' . $id . ' not found.'], 404);
            }

            // Access the source_id value for the pivot
            $source = $game->gameSources()->where(function ($q) use ($id) {
                $q->where('game_source_id', $this->api->sourceId)->where('game_id', $id);
            })->first()->pivot;

            if (!$source) {
                return response(['message' => 'Source for game #' . $id . ' not found.'], 404);
            }

            $results = $this->api->head2head(435951);

            $matchesData = $results->matches;

            $country_not_found = [];
            $competition_not_found = [];
            $home_team_not_found = [];
            $away_team_not_found = [];
            $saved = 0;
            $updated = 0;
            $season = null;
            foreach ($matchesData as $match) {

                $country = $match->area;
                $country = Country::where('name', $country->name)->where('code', $country->code)->first();

                if (!$country->id) {

                    if (!isset($country_not_found[$country->name])) {
                        $country_not_found[$country->name] = 1;
                    } else {
                        $country_not_found[$country->name] = $country_not_found[$country->name] + 1;
                    }

                    Log::critical('Searched country not found',  (array) $match->area);
                    continue;
                }

                $homeTeam = Team::whereHas('gameSources', function ($q) use ($match) {
                    $q->where('source_id', $match->homeTeam->id);
                })->first();

                $awayTeam = Team::whereHas('gameSources', function ($q) use ($match) {
                    $q->where('source_id', $match->awayTeam->id);
                })->first();

                if ($homeTeam && $awayTeam) {

                    // If source competition is different from current one in subject then we want to find this compe in db, if not found we continue to the next loop pointer
                    if ($source->source_id !== $match->competition->id) {

                        $competition = Competition::whereHas('gameSources', function ($q) use ($match) {
                            $q->where('game_source_id', $this->api->sourceId)->where('source_id', $match->competition->id);
                        })->with(['gameSources' => function ($q) use ($match) {
                            $q->where('game_source_id', $this->api->sourceId)->where('source_id', $match->competition->id);
                        }])->first();

                        if (!$competition->id) {

                            if (!isset($competition_not_found[$country->name])) {
                                $competition_not_found[$match->competition->name] = 1;
                            } else {
                                $competition_not_found[$match->competition->name] = $competition_not_found[$match->competition->name] + 1;
                            }

                            Log::critical('Searched competition not found',  (array) $match->competition);
                            continue;
                        }
                    }

                    $p = null;
                    
                    // All is set can now save game!
                    $this->saveGame($match, $country, $competition, $season, $homeTeam, $awayTeam, $saved, $updated, $p);
                } else {

                    if (!$homeTeam) {

                        if (!isset($home_team_not_found[$country->name])) {
                            $home_team_not_found[$match->homeTeam->name] = 1;
                        } else {
                            $home_team_not_found[$match->homeTeam->name] = $home_team_not_found[$match->homeTeam->name] + 1;
                        }

                        Log::critical('homeTeam not found:', (array) $match->homeTeam);
                    }

                    if (!$awayTeam) {

                        if (!isset($away_team_not_found[$country->name])) {
                            $away_team_not_found[$match->awayTeam->name] = 1;
                        } else {
                            $away_team_not_found[$match->awayTeam->name] = $away_team_not_found[$match->awayTeam->name] + 1;
                        }

                        Log::critical('awayTeam not found:', (array) $match->awayTeam);
                    }
                }
            }

            DB::commit();

            $msg = "Fetching matches completed, (saved $saved, updated: $updated).";

            if (count($country_not_found) > 0) {
                $msg .= ' ' . count($country_not_found) . ' countries were not found.';
            }

            if (count($competition_not_found) > 0) {
                $msg .= ' ' . count($competition_not_found) . ' competitions were not found.';
            }

            if (count($home_team_not_found) > 0) {
                $msg .= ' ' . count($home_team_not_found) . ' home teams were not found.';
            }

            if (count($away_team_not_found) > 0) {
                $msg .= ' ' . count($away_team_not_found) . ' away teams were not found.';
            }

            return response(['message' => $msg]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error during data import: ' . $e->getMessage() . ', File: ' . $e->getFile() . ', Line no:' . $e->getLine(),);
            return response(['message' => 'Error during data import.'], 500);
        }
    }

    private function saveGame($match, $country, $competition, &$season, $homeTeam, $awayTeam, &$saved, &$updated, $played = null)
    {

        $seasonData = $match->season;
        // let us get season 
        if (!$season || $season->start_date != $seasonData->startDate) {
            $season = app(Seasons::class)->updateOrCreate($seasonData, $country, $competition, $is_current = false, $played);
        }

        $competition_id = $competition->id;
        $season_id = $season->id;
        $country_id = $country->id;
        $date = Carbon::parse($match->utcDate)->format('Y-m-d');
        $utc_date = Carbon::parse($match->utcDate)->toDateTimeString();
        $status = $match->status;
        $matchday = $match->matchday;
        $stage = $match->stage;
        $group = $match->group;
        $last_updated = Carbon::parse($match->lastUpdated)->toDateTimeString();
        $last_fetch = Carbon::now();
        $status_id = activeStatusId();
        $user_id = auth()->id();

        $commonArr = [
            'competition_id' => $competition_id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'season_id' => $season_id,
            'country_id' => $country_id,
            'date' => $date,
            'utc_date' => $utc_date,
        ];

        $game = Game::updateOrCreate(
            $commonArr,
            array_merge(
                $commonArr,
                [
                    'status' => $status,
                    'matchday' => $matchday,
                    'stage' => $stage,
                    'group' => $group,
                    'last_updated' => $last_updated,
                    'last_fetch' => $last_fetch,
                    'status_id' => $status_id,
                    'user_id' => $user_id,
                ]
            )
        );

        if ($game) {

            if ($game->wasRecentlyCreated) {
                $saved++;
            } else {
                $updated++;
            }

            // sync referees
            $referees = $match->referees;
            $refsArr = [];
            foreach ($referees as $referee) {

                $country = Country::where('name', $referee->nationality)->first();

                $ref = Referee::updateOrCreate([
                    'name' => $referee->name,
                    'type' => $referee->type,
                    'country_id' => $country->id ?? 0,
                ]);

                if ($ref) {
                    $refsArr[] = $ref->id;
                }
            }

            $game->referees()->sync($refsArr);

            // Check if the game source with the given ID doesn't exist
            if (!$game->gameSources()->where('game_source_id', $this->api->sourceId)->where('game_id', $game->id)->exists()) {
                // Attach the relationship with the URI
                $game->gameSources()->attach($this->api->sourceId, ['source_id' => $match->id]);
            }

            $this->storeScores($game, $match->score);
        }
    }

    function storeScores($game, $score)
    {

        $full_time = (array) $score->fullTime;
        $half_time = (array) $score->halfTime;

        if ($score->winner == null && count(array_filter($full_time)) === 0 && count(array_filter($half_time)) === 0) return false;

        $winner = $score->winner;
        $duration = $score->duration;

        $home_scores_full_time = $full_time['home'];
        $away_scores_full_time = $full_time['away'];

        $home_scores_half_time = $half_time['home'];
        $away_scores_half_time = $half_time['away'];

        GameScore::updateOrCreate(
            [
                'game_id' => $game->id
            ],
            [
                'game_id' => $game->id,
                'winner' => $winner,
                'duration' => $duration,

                'home_scores_full_time' => $home_scores_full_time,
                'away_scores_full_time' => $away_scores_full_time,
                'home_scores_half_time' => $home_scores_half_time,
                'away_scores_half_time' => $away_scores_half_time,
            ]
        );
    }
}
