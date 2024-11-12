<?php

namespace App\Utilities;

use App\Http\Controllers\Dashboard\Teams\View\TeamController;
use App\Models\Team;
use App\Repositories\GameComposer;
use App\Repositories\Team\TeamRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GameStatsUtility
{
    protected $teamRepositoryInterface;

    function __construct()
    {

        $this->teamRepositoryInterface = new TeamRepository(new Team());
    }

    // Add this new method to calculate and add game statistics
    function addGameStatistics($matchData)
    {

        $home_team_id = $matchData['home_team_id'];

        $away_team_id = $matchData['away_team_id'];

        // Calculate the winner
        $winningSide = GameComposer::winningSide($matchData, true);
        $ft_hda_target = $winningSide;

        $winningSide = GameComposer::winningSideHT($matchData, true);
        $ht_hda_target = $winningSide;

        // Calculate if both teams scored (bts)
        $bts = GameComposer::bts($matchData);

        $bts_target = $bts ? 1 : 0;

        // Calculate the total number of goals
        $goals = GameComposer::goals($matchData);

        $over15_target = ($goals > 1) ? 1 : 0;
        $over25_target = ($goals > 2) ? 1 : 0;
        $over35_target = ($goals > 3) ? 1 : 0;

        $cs_target = game_scores($matchData->score);

        $referees_ids = array_reduce($matchData->referees()->pluck('referees.id')->toArray(), fn($p, $c) => $p + $c, 0);

        // Get home and away team matches and calculate statistics

        $to_date = Carbon::parse($matchData->utc_date)->subDay()->format('Y-m-d');

        $teamsStats = $this->teamStats($to_date, $home_team_id, $away_team_id);
        if ($teamsStats === null) return null;

        $teamsStatsRecent = $this->teamStatsCurrentground($to_date, $home_team_id, $away_team_id);
        if ($teamsStatsRecent === null) return null;

        $teamsHead2HeadStats = $this->teamsHead2HeadStats($to_date, $home_team_id, $away_team_id, $matchData->id);

        $lp_details = $this->teamRepositoryInterface->teamLeagueDetails($matchData->home_team_id, $matchData->id);
        $team_lp_home_team_details = [
            'team_lp_home_team_position' => $lp_details['position'],
            'team_lp_home_team_played_games' => $lp_details['played_games'],
            'team_lp_home_team_won' => $lp_details['won'],
            'team_lp_home_team_lost' => $lp_details['lost'],
            'team_lp_home_team_points' => $lp_details['points'],
            'team_lp_home_team_goals_for' => $lp_details['goals_for'],
            'team_lp_home_team_goals_against' => $lp_details['goals_against'],
            'team_lp_home_team_goal_difference' => $lp_details['goal_difference'],
        ];

        $lp_details = $this->teamRepositoryInterface->teamLeagueDetails($matchData->away_team_id, $matchData->id);
        $team_lp_away_team_details = [
            'team_lp_away_team_position' => $lp_details['position'],
            'team_lp_away_team_played_games' => $lp_details['played_games'],
            'team_lp_away_team_won' => $lp_details['won'],
            'team_lp_away_team_lost' => $lp_details['lost'],
            'team_lp_away_team_points' => $lp_details['points'],
            'team_lp_away_team_goals_for' => $lp_details['goals_for'],
            'team_lp_away_team_goals_against' => $lp_details['goals_against'],
            'team_lp_away_team_goal_difference' => $lp_details['goal_difference'],
        ];

        $match_odds = $this->getMatchOdds($matchData);

        return array_merge(
            $teamsStats,
            $teamsStatsRecent,
            $teamsHead2HeadStats,
            [
                'has_results' => GameComposer::hasResults($matchData),
                'has_results_ht' => GameComposer::hasResultsHT($matchData),
                'ft_hda_target' => $ft_hda_target,
                'ht_hda_target' => $ht_hda_target,
                'over15_target' => $over15_target,
                'over25_target' => $over25_target,
                'over35_target' => $over35_target,
                'bts_target' => $bts_target,
                'cs_target' => $cs_target,
                'referees_ids' => $referees_ids,
            ],
            $team_lp_home_team_details,
            $team_lp_away_team_details,
            $match_odds,
        );
    }

    private function teamStats($to_date, $home_team_id, $away_team_id)
    {

        $per_page = request()->history_limit_per_match ?? 10;

        $all_params = [
            'team_id' => $home_team_id,
            'team_ids' => null,
            'playing' => 'all',
            'to_date' => $to_date,
            'currentground' => null,
            'season_id' => null,
            'type' => null,
            'order_by' => 'utc_date',
            'order_direction' => 'desc',
            'per_page' => $per_page,
            'without_response' => true,
        ];

        request()->merge(['all_params' => $all_params]);
        $threshold = floor($per_page * .8);

        $home_team_matches = app(TeamController::class)->matches($home_team_id)['data'];

        // Log::info('threshold: ' . $threshold . ' ct-->' . count($home_team_matches));

        if (count($home_team_matches) < $threshold) return null;


        $all_params['team_id'] = $away_team_id;
        request()->merge(['all_params' => $all_params]);

        $away_team_matches = app(TeamController::class)->matches($away_team_id)['data'];

        if (count($away_team_matches) < $threshold) return null;

        $home_team_matches_with_stats = $this->calculateTeamStats($home_team_matches, $home_team_id);
        $away_team_matches_with_stats = $this->calculateTeamStats($away_team_matches, $away_team_id);

        return [
            'home_team_totals' => $home_team_matches_with_stats['totals'],
            'home_team_wins' => $home_team_matches_with_stats['teamWins'],
            'home_team_draws' => $home_team_matches_with_stats['draws'],
            'home_team_loses' => $home_team_matches_with_stats['teamLoses'],
            'home_team_goals_for' => $home_team_matches_with_stats['goalsFor'],
            'home_team_goals_for_avg' => $home_team_matches_with_stats['goalsForAvg'],
            'home_team_goals_against' => $home_team_matches_with_stats['goalsAgainst'],
            'home_team_goals_against_avg' => $home_team_matches_with_stats['goalsAgainstAvg'],
            'home_team_bts_games' => $home_team_matches_with_stats['bts_games'],
            'home_team_over15_games' => $home_team_matches_with_stats['over15_games'],
            'home_team_over25_games' => $home_team_matches_with_stats['over25_games'],
            'home_team_over35_games' => $home_team_matches_with_stats['over35_games'],

            'away_team_totals' => $away_team_matches_with_stats['totals'],
            'away_team_wins' => $away_team_matches_with_stats['teamWins'],
            'away_team_draws' => $away_team_matches_with_stats['draws'],
            'away_team_loses' => $away_team_matches_with_stats['teamLoses'],
            'away_team_goals_for' => $away_team_matches_with_stats['goalsFor'],
            'away_team_goals_for_avg' => $away_team_matches_with_stats['goalsForAvg'],
            'away_team_goals_against' => $away_team_matches_with_stats['goalsAgainst'],
            'away_team_goals_against_avg' => $away_team_matches_with_stats['goalsAgainstAvg'],
            'away_team_bts_games' => $away_team_matches_with_stats['bts_games'],
            'away_team_over15_games' => $away_team_matches_with_stats['over15_games'],
            'away_team_over25_games' => $away_team_matches_with_stats['over25_games'],
            'away_team_over35_games' => $away_team_matches_with_stats['over35_games'],

            'ht_home_team_totals' => $home_team_matches_with_stats['ht_totals'],
            'ht_home_team_wins' => $home_team_matches_with_stats['ht_teamWins'],
            'ht_home_team_draws' => $home_team_matches_with_stats['ht_draws'],
            'ht_home_team_loses' => $home_team_matches_with_stats['ht_teamLoses'],
            'ht_home_team_goals_for' => $home_team_matches_with_stats['ht_goalsFor'],
            'ht_home_team_goals_for_avg' => $home_team_matches_with_stats['ht_goalsForAvg'],
            'ht_home_team_goals_against' => $home_team_matches_with_stats['ht_goalsAgainst'],
            'ht_home_team_goals_against_avg' => $home_team_matches_with_stats['ht_goalsAgainstAvg'],
            'ht_home_team_bts_games' => $home_team_matches_with_stats['ht_bts_games'],
            'ht_home_team_over15_games' => $home_team_matches_with_stats['ht_over15_games'],
            'ht_home_team_over25_games' => $home_team_matches_with_stats['ht_over25_games'],
            'ht_home_team_over35_games' => $home_team_matches_with_stats['ht_over35_games'],

            'ht_away_team_totals' => $away_team_matches_with_stats['ht_totals'],
            'ht_away_team_wins' => $away_team_matches_with_stats['ht_teamWins'],
            'ht_away_team_draws' => $away_team_matches_with_stats['ht_draws'],
            'ht_away_team_loses' => $away_team_matches_with_stats['ht_teamLoses'],
            'ht_away_team_goals_for' => $away_team_matches_with_stats['ht_goalsFor'],
            'ht_away_team_goals_for_avg' => $away_team_matches_with_stats['ht_goalsForAvg'],
            'ht_away_team_goals_against' => $away_team_matches_with_stats['ht_goalsAgainst'],
            'ht_away_team_goals_against_avg' => $away_team_matches_with_stats['ht_goalsAgainstAvg'],
            'ht_away_team_bts_games' => $away_team_matches_with_stats['ht_bts_games'],
            'ht_away_team_over15_games' => $away_team_matches_with_stats['ht_over15_games'],
            'ht_away_team_over25_games' => $away_team_matches_with_stats['ht_over25_games'],
            'ht_away_team_over35_games' => $away_team_matches_with_stats['ht_over35_games'],

        ];
    }

    private function teamStatsCurrentground($to_date, $home_team_id, $away_team_id)
    {
        return [];

        $per_page = 4;
        if (request()->current_ground_limit_per_match)
            $per_page = request()->current_ground_limit_per_match;
        $per_page = $per_page < 4 ? 4 : $per_page;

        $all_params = [
            'team_id' => $home_team_id,
            'team_ids' => null,
            'playing' => 'all',
            'to_date' => $to_date,
            'currentground' => 'home',
            'season_id' => null,
            'type' => null,
            'order_by' => 'utc_date',
            'order_direction' => 'desc',
            'per_page' => $per_page,
            'without_response' => true,
        ];

        request()->merge(['all_params' => $all_params]);
        $threshold = floor($per_page * .8);

        $home_team_matches = app(TeamController::class)->matches($home_team_id)['data'];
        if (count($home_team_matches) < $threshold) return null;

        $all_params['currentground'] = 'away';
        $all_params['team_id'] = $home_team_id;
        request()->merge(['all_params' => $all_params]);

        $away_team_matches = app(TeamController::class)->matches($away_team_id)['data'];
        if (count($away_team_matches) < $threshold) return null;

        $home_team_matches_with_stats = $this->calculateTeamStats($home_team_matches, $home_team_id);
        $away_team_matches_with_stats = $this->calculateTeamStats($away_team_matches, $away_team_id);
        $home_team_matches_with_stats = [];
        $away_team_matches_with_stats = [];

        return [
            'current_ground_home_team_totals' => $home_team_matches_with_stats['totals'] ?? 0,
            'current_ground_home_team_wins' => $home_team_matches_with_stats['teamWins'] ?? 0,
            'current_ground_home_team_draws' => $home_team_matches_with_stats['draws'] ?? 0,
            'current_ground_home_team_loses' => $home_team_matches_with_stats['teamLoses'] ?? 0,
            'current_ground_home_team_goals_for' => $home_team_matches_with_stats['goalsFor'] ?? 0,
            'current_ground_home_team_goals_for_avg' => $home_team_matches_with_stats['goalsForAvg'] ?? 0,
            'current_ground_home_team_goals_against' => $home_team_matches_with_stats['goalsAgainst'] ?? 0,
            'current_ground_home_team_goals_against_avg' => $home_team_matches_with_stats['goalsAgainstAvg'] ?? 0,
            'current_ground_home_team_bts_games' => $home_team_matches_with_stats['bts_games'] ?? 0,
            'current_ground_home_team_over15_games' => $home_team_matches_with_stats['over15_games'] ?? 0,
            'current_ground_home_team_over25_games' => $home_team_matches_with_stats['over25_games'] ?? 0,
            'current_ground_home_team_over35_games' => $home_team_matches_with_stats['over35_games'] ?? 0,

            'current_ground_away_team_totals' => $away_team_matches_with_stats['totals'] ?? 0,
            'current_ground_away_team_wins' => $away_team_matches_with_stats['teamWins'] ?? 0,
            'current_ground_away_team_draws' => $away_team_matches_with_stats['draws'] ?? 0,
            'current_ground_away_team_loses' => $away_team_matches_with_stats['teamLoses'] ?? 0,
            'current_ground_away_team_goals_for' => $away_team_matches_with_stats['goalsFor'] ?? 0,
            'current_ground_away_team_goals_for_avg' => $away_team_matches_with_stats['goalsForAvg'] ?? 0,
            'current_ground_away_team_goals_against' => $away_team_matches_with_stats['goalsAgainst'] ?? 0,
            'current_ground_away_team_goals_against_avg' => $away_team_matches_with_stats['goalsAgainstAvg'] ?? 0,
            'current_ground_away_team_bts_games' => $away_team_matches_with_stats['bts_games'] ?? 0,
            'current_ground_away_team_over15_games' => $away_team_matches_with_stats['over15_games'] ?? 0,
            'current_ground_away_team_over25_games' => $away_team_matches_with_stats['over25_games'] ?? 0,
            'current_ground_away_team_over35_games' => $away_team_matches_with_stats['over35_games'] ?? 0,

            'current_ground_ht_home_team_totals' => $home_team_matches_with_stats['ht_totals'] ?? 0,
            'current_ground_ht_home_team_wins' => $home_team_matches_with_stats['ht_teamWins'] ?? 0,
            'current_ground_ht_home_team_draws' => $home_team_matches_with_stats['ht_draws'] ?? 0,
            'current_ground_ht_home_team_loses' => $home_team_matches_with_stats['ht_teamLoses'] ?? 0,
            'current_ground_ht_home_team_goals_for' => $home_team_matches_with_stats['ht_goalsFor'] ?? 0,
            'current_ground_ht_home_team_goals_for_avg' => $home_team_matches_with_stats['ht_goalsForAvg'] ?? 0,
            'current_ground_ht_home_team_goals_against' => $home_team_matches_with_stats['ht_goalsAgainst'] ?? 0,
            'current_ground_ht_home_team_goals_against_avg' => $home_team_matches_with_stats['ht_goalsAgainstAvg'] ?? 0,
            'current_ground_ht_home_team_bts_games' => $home_team_matches_with_stats['ht_bts_games'] ?? 0,
            'current_ground_ht_home_team_over15_games' => $home_team_matches_with_stats['ht_over15_games'] ?? 0,
            'current_ground_ht_home_team_over25_games' => $home_team_matches_with_stats['ht_over25_games'] ?? 0,
            'current_ground_ht_home_team_over35_games' => $home_team_matches_with_stats['ht_over35_games'] ?? 0,

            'current_ground_ht_away_team_totals' => $away_team_matches_with_stats['ht_totals'] ?? 0,
            'current_ground_ht_away_team_wins' => $away_team_matches_with_stats['ht_teamWins'] ?? 0,
            'current_ground_ht_away_team_draws' => $away_team_matches_with_stats['ht_draws'] ?? 0,
            'current_ground_ht_away_team_loses' => $away_team_matches_with_stats['ht_teamLoses'] ?? 0,
            'current_ground_ht_away_team_goals_for' => $away_team_matches_with_stats['ht_goalsFor'] ?? 0,
            'current_ground_ht_away_team_goals_for_avg' => $away_team_matches_with_stats['ht_goalsForAvg'] ?? 0,
            'current_ground_ht_away_team_goals_against' => $away_team_matches_with_stats['ht_goalsAgainst'] ?? 0,
            'current_ground_ht_away_team_goals_against_avg' => $away_team_matches_with_stats['ht_goalsAgainstAvg'] ?? 0,
            'current_ground_ht_away_team_bts_games' => $away_team_matches_with_stats['ht_bts_games'] ?? 0,
            'current_ground_ht_away_team_over15_games' => $away_team_matches_with_stats['ht_over15_games'] ?? 0,
            'current_ground_ht_away_team_over25_games' => $away_team_matches_with_stats['ht_over25_games'] ?? 0,
            'current_ground_ht_away_team_over35_games' => $away_team_matches_with_stats['ht_over35_games'] ?? 0,

        ];
    }

    private function teamsHead2HeadStats($to_date, $home_team_id, $away_team_id, $match_id)
    {
        $per_page = 4;
        if (request()->h2h_limit_per_match)
            $per_page = request()->h2h_limit_per_match;
        $per_page = $per_page < 4 ? 4 : $per_page;

        $all_params = [
            'team_id' => null,
            'team_ids' => null,
            'playing' => 'all',
            'to_date' => $to_date,
            'currentground' => null,
            'season_id' => null,
            'type' => null,
            'order_by' => 'utc_date',
            'order_direction' => 'desc',
            'per_page' => $per_page,
            'without_response' => true,
        ];

        request()->merge(['all_params' => $all_params]);

        $matches = app(TeamController::class)->head2head($match_id)['data'];

        $home_team_matches_with_stats = $this->calculateTeamStats($matches, $home_team_id);
        $away_team_matches_with_stats = $this->calculateTeamStats($matches, $away_team_id);

        return [
            'h2h_home_team_totals' => $home_team_matches_with_stats['totals'],
            'h2h_home_team_wins' => $home_team_matches_with_stats['teamWins'],
            'h2h_home_team_draws' => $home_team_matches_with_stats['draws'],
            'h2h_home_team_loses' => $home_team_matches_with_stats['teamLoses'],
            'h2h_home_team_goals_for' => $home_team_matches_with_stats['goalsFor'],
            'h2h_home_team_goals_for_avg' => $home_team_matches_with_stats['goalsForAvg'],
            'h2h_home_team_goals_against' => $home_team_matches_with_stats['goalsAgainst'],
            'h2h_home_team_goals_against_avg' => $home_team_matches_with_stats['goalsAgainstAvg'],
            'h2h_home_team_bts_games' => $home_team_matches_with_stats['bts_games'],
            'h2h_home_team_over15_games' => $home_team_matches_with_stats['over15_games'],
            'h2h_home_team_over25_games' => $home_team_matches_with_stats['over25_games'],
            'h2h_home_team_over35_games' => $home_team_matches_with_stats['over35_games'],

            'h2h_away_team_totals' => $away_team_matches_with_stats['totals'],
            'h2h_away_team_wins' => $away_team_matches_with_stats['teamWins'],
            'h2h_away_team_draws' => $away_team_matches_with_stats['draws'],
            'h2h_away_team_loses' => $away_team_matches_with_stats['teamLoses'],
            'h2h_away_team_goals_for' => $away_team_matches_with_stats['goalsFor'],
            'h2h_away_team_goals_for_avg' => $away_team_matches_with_stats['goalsForAvg'],
            'h2h_away_team_goals_against' => $away_team_matches_with_stats['goalsAgainst'],
            'h2h_away_team_goals_against_avg' => $away_team_matches_with_stats['goalsAgainstAvg'],
            'h2h_away_team_bts_games' => $away_team_matches_with_stats['bts_games'],
            'h2h_away_team_over15_games' => $away_team_matches_with_stats['over15_games'],
            'h2h_away_team_over25_games' => $away_team_matches_with_stats['over25_games'],
            'h2h_away_team_over35_games' => $away_team_matches_with_stats['over35_games'],

            'h2h_ht_home_team_totals' => $home_team_matches_with_stats['ht_totals'],
            'h2h_ht_home_team_wins' => $home_team_matches_with_stats['ht_teamWins'],
            'h2h_ht_home_team_draws' => $home_team_matches_with_stats['ht_draws'],
            'h2h_ht_home_team_loses' => $home_team_matches_with_stats['ht_teamLoses'],
            'h2h_ht_home_team_goals_for' => $home_team_matches_with_stats['ht_goalsFor'],
            'h2h_ht_home_team_goals_for_avg' => $home_team_matches_with_stats['ht_goalsForAvg'],
            'h2h_ht_home_team_goals_against' => $home_team_matches_with_stats['ht_goalsAgainst'],
            'h2h_ht_home_team_goals_against_avg' => $home_team_matches_with_stats['ht_goalsAgainstAvg'],
            'h2h_ht_home_team_bts_games' => $home_team_matches_with_stats['ht_bts_games'],
            'h2h_ht_home_team_over15_games' => $home_team_matches_with_stats['ht_over15_games'],
            'h2h_ht_home_team_over25_games' => $home_team_matches_with_stats['ht_over25_games'],
            'h2h_ht_home_team_over35_games' => $home_team_matches_with_stats['ht_over35_games'],

            'h2h_ht_away_team_totals' => $away_team_matches_with_stats['ht_totals'],
            'h2h_ht_away_team_wins' => $away_team_matches_with_stats['ht_teamWins'],
            'h2h_ht_away_team_draws' => $away_team_matches_with_stats['ht_draws'],
            'h2h_ht_away_team_loses' => $away_team_matches_with_stats['ht_teamLoses'],
            'h2h_ht_away_team_goals_for' => $away_team_matches_with_stats['ht_goalsFor'],
            'h2h_ht_away_team_goals_for_avg' => $away_team_matches_with_stats['ht_goalsForAvg'],
            'h2h_ht_away_team_goals_against' => $away_team_matches_with_stats['ht_goalsAgainst'],
            'h2h_ht_away_team_goals_against_avg' => $away_team_matches_with_stats['ht_goalsAgainstAvg'],
            'h2h_ht_away_team_bts_games' => $away_team_matches_with_stats['ht_bts_games'],
            'h2h_ht_away_team_over15_games' => $away_team_matches_with_stats['ht_over15_games'],
            'h2h_ht_away_team_over25_games' => $away_team_matches_with_stats['ht_over25_games'],
            'h2h_ht_away_team_over35_games' => $away_team_matches_with_stats['ht_over35_games'],

        ];
    }

    function calculateTeamStats($teamGames, $teamId, $increment = 1)
    {
        $totals = 0;
        $teamWins = 0;
        $draws = 0;
        $teamLoses = 0;
        $goalsFor = 0;
        $goalsAgainst = 0;
        $goalsForAvg = 0;
        $goalsAgainstAvg = 0;
        $bts_games = 0;
        $over15_games = 0;
        $over25_games = 0;
        $over35_games = 0;

        // Half time
        $ht_totals = 0;
        $ht_teamWins = 0;
        $ht_draws = 0;
        $ht_teamLoses = 0;
        $ht_goalsFor = 0;
        $ht_goalsAgainst = 0;
        $ht_goalsForAvg = 0;
        $ht_goalsAgainstAvg = 0;
        $ht_bts_games = 0;
        $ht_over15_games = 0;
        $ht_over25_games = 0;
        $ht_over35_games = 0;


        if (!empty($teamGames)) {
            foreach ($teamGames as $game) {

                $hasResults = GameComposer::hasResults($game);

                if ($hasResults) {
                    $totals += 1;

                    $winningSide = GameComposer::winningSide($game, true);
                    if ($winningSide === 1) {
                        $draws += $increment;
                    } elseif ($winningSide === 0 || $winningSide === 2) {
                        $winnerId = GameComposer::winnerId($game);
                        if ($winnerId == $teamId) {
                            $teamWins += $increment;
                        } else {
                            $teamLoses += $increment;
                        }
                    }

                    // Calculate if both teams scored (bts)
                    $bts = GameComposer::bts($game);

                    $bts_target = $bts ? 1 : 0;

                    // Calculate the total number of goals
                    $goals = GameComposer::goals($game);

                    $over15_target = ($goals > 1) ? 1 : 0;
                    $over25_target = ($goals > 2) ? 1 : 0;
                    $over35_target = ($goals > 3) ? 1 : 0;


                    $bts_games += $bts_target;
                    $over15_games += $over15_target;
                    $over25_games += $over25_target;
                    $over35_games += $over35_target;

                    $hasResultsHT = GameComposer::hasResultsHT($game);
                    if ($hasResultsHT) {
                        $ht_totals += 1;

                        // for ht
                        $winningSide = GameComposer::winningSideHT($game, true);
                        if ($winningSide === 1) {
                            $ht_draws += $increment;
                        } elseif ($winningSide === 0 || $winningSide === 2) {
                            $winnerId = GameComposer::winnerIdHT($game);
                            if ($winnerId == $teamId) {
                                $ht_teamWins += $increment;
                            } else {
                                $ht_teamLoses += $increment;
                            }
                        }

                        // Calculate if both teams scored (bts)
                        $bts = GameComposer::btsHT($game);

                        $bts_target = $bts ? 1 : 0;

                        // Calculate the total number of goals
                        $goals = GameComposer::goalsHT($game);

                        $over15_target = ($goals > 1) ? 1 : 0;
                        $over25_target = ($goals > 2) ? 1 : 0;
                        $over35_target = ($goals > 3) ? 1 : 0;


                        $ht_bts_games += $bts_target;
                        $ht_over15_games += $over15_target;
                        $ht_over25_games += $over25_target;
                        $ht_over35_games += $over35_target;

                        // Get goals for and goals against
                        $goalsFor += (GameComposer::getScores($game, $teamId) * $increment);
                        $goalsAgainst += (GameComposer::getScores($game, $teamId, true) * $increment);
                        $ht_goalsFor += (GameComposer::getScoresHT($game, $teamId) * $increment);
                        $ht_goalsAgainst += (GameComposer::getScoresHT($game, $teamId, true) * $increment);
                    }
                }
            }

            // Calculate averages
            $goalsForAvg = $totals > 0 ? round($goalsFor / $totals, 2) : 0;
            $goalsAgainstAvg = $totals > 0 ? round($goalsAgainst / $totals, 2) : 0;

            // averages for ht
            $ht_goalsForAvg = $ht_totals > 0 ? round($ht_goalsFor / $ht_totals, 2) : 0;
            $ht_goalsAgainstAvg = $ht_totals > 0 ? round($ht_goalsAgainst / $ht_totals, 2) : 0;
        }

        return [
            'totals' => $totals,
            'teamWins' => $teamWins,
            'draws' => $draws,
            'teamLoses' => $teamLoses,
            'goalsFor' => $goalsFor,
            'goalsAgainst' => $goalsAgainst,
            'goalsForAvg' => $goalsForAvg,
            'goalsAgainstAvg' => $goalsAgainstAvg,
            'bts_games' => $bts_games,
            'over15_games' => $over15_games,
            'over25_games' => $over25_games,
            'over35_games' => $over35_games,

            'ht_totals' => $ht_totals,
            'ht_teamWins' => $ht_teamWins,
            'ht_draws' => $ht_draws,
            'ht_teamLoses' => $ht_teamLoses,
            'ht_goalsFor' => $ht_goalsFor,
            'ht_goalsAgainst' => $ht_goalsAgainst,
            'ht_goalsForAvg' => $ht_goalsForAvg,
            'ht_goalsAgainstAvg' => $ht_goalsAgainstAvg,
            'ht_bts_games' => $ht_bts_games,
            'ht_over15_games' => $ht_over15_games,
            'ht_over25_games' => $ht_over25_games,
            'ht_over35_games' => $ht_over35_games,
        ];
    }

    private function getMatchOdds($game)
    {

        $odds = $game->odds()->where('home_win_odds', '>', 0)->first();

        return [
            'home_win_odds' => $odds->home_win_odds ?? 0,
            'draw_odds' => $odds->draw_odds ?? 0,
            'away_win_odds' => $odds->away_win_odds ?? 0,
            'over_25_odds' => $odds->over_25_odds ?? 0,
            'under_25_odds' => $odds->under_25_odds ?? 0,
            'gg_odds' => $odds->gg_odds ?? 0,
            'ng_odds' => $odds->ng_odds ?? 0,
        ];
    }
}
