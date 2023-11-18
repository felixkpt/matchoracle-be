<?php

namespace App\Repositories\Game;

use App\Http\Controllers\Admin\Teams\View\TeamController;
use App\Models\Game;
use App\Models\GameVote;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\GameComposer;
use App\Repositories\SearchRepo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameRepository implements GameRepositoryInterface
{

    use CommonRepoActions;

    protected $sourceContext;

    function __construct(protected Game $model)
    {
    }

    public function index($id = null, $without_response = null)
    {
        $seasons = null;
        if (isset(request()->season)) {
            $seasons = Season::where("start_date", 'like', request()->season . '-%')
                ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
                ->get()->pluck('id');
        }

        $homeWinVotes = function ($q) {
            return $q->votes->where('winner', 'home')->count();
        };

        $drawVotes = function ($q) {
            return $q->votes->where('winner', 'draw')->count();
        };

        $awayWinVotes = function ($q) {
            return $q->votes->where('winner', 'away')->count();
        };

        $prediction = function ($q) {

            $pred = $q->prediction;
            if ($pred) {
                $cs = array_search($pred->cs, scores());
                $pred->hda = $pred->hda == 1 ? '1' : ($pred->hda == 0 ? 'X' : '2');
                $pred->cs = $cs;
                $pred->bts = $pred->gg == 1 ? 'YES' : 'NO';
                $pred->over25 = $pred->over25 == 1 ? 'OV' : 'UN';
             
                return $pred;
            }

            return null;
        };

        $currentUserVotes = function ($q) {
            return !!$q->votes->where(function ($q) {
                return $q->where('user_id', auth()->id())->orWhere('user_ip', request()->ip());
            })->whereNotNull('winner')->first();
        };

        $teamsMatch = function ($q) {

            return $q->where(function ($q) {
                [$home_team_id, $away_team_id] = request()->team_ids;
                $arr = [['home_team_id', $home_team_id], ['away_team_id', $away_team_id]];
                $arr2 = [['home_team_id', $away_team_id], ['away_team_id', $home_team_id]];

                if (request()->playing == 'home-away') {
                    $q->where($arr);
                } else {
                    $q->where($arr)->orWhere($arr2);
                }
            });
        };
        Log::alert('dd', [request()->date]);

        $before = request()->before ?? Carbon::now();
        $competitions = $this->model::with(['competition' => fn ($q) => $q->with(['country', 'currentSeason']), 'home_team', 'away_team', 'score', 'votes', 'referees'])
            ->when(request()->country_id, fn ($q) => $q->where('country_id', request()->country_id))
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when(request()->team_id, fn ($q) => $q->where(fn ($q) => $q->where('home_team_id', request()->team_id)->orWhere('away_team_id', request()->team_id)))
            ->when(request()->team_ids, $teamsMatch)
            ->when(request()->currentground, fn ($q) => request()->currentground == 'home' ? $q->where('home_team_id', request()->team_id) : (request()->currentground == 'away' ? $q->where('away_team_id', request()->team_id) :  $q))
            ->when($seasons, fn ($q) => $q->whereIn('season_id', $seasons))
            ->when(request()->type, fn ($q) => request()->type == 'played' ? $q->where('utc_date', '<', $before) : (request()->type == 'upcoming' ? $q->where('utc_date', '>=', Carbon::now()) :  $q))
            ->when(request()->yesterday, fn ($q) => $q->whereDate('utc_date', Carbon::yesterday()))
            ->when(request()->today, fn ($q) => $q->whereDate('utc_date', Carbon::today()))
            ->when(request()->tomorrow, fn ($q) => $q->whereDate('utc_date', Carbon::tomorrow()))
            ->when(request()->year, fn ($q) => $q->whereYear('utc_date', request()->year))
            ->when(request()->from_date, fn ($q) => $q->whereDate('utc_date', '>=', Carbon::parse(request()->from_date)->format('Y-m-d')))
            ->when(request()->to_date, fn ($q) => $q->whereDate('utc_date', '<=', Carbon::parse(request()->to_date)->format('Y-m-d')))
            ->when(request()->date, fn ($q) => $q->whereDate('utc_date', '=', Carbon::parse(request()->date)->format('Y-m-d')))
            ->when((request()->year && request()->month), function ($q) {
                return $q
                    ->whereYear('utc_date', request()->year)
                    ->whereMonth('utc_date', request()->month);
            })->when((request()->year && request()->month && request()->day), function ($q) {
                return $q
                    ->whereYear('utc_date', request()->year)
                    ->whereMonth('utc_date', request()->month)
                    ->whereDay('utc_date', request()->day);
            })
            ->when($id, fn ($q) => $q->where('id', $id));

        $uri = '/admin/matches/';
        $results = SearchRepo::of($competitions, ['id', 'name'])
            ->addColumn('ID', fn ($q) => '<a class="dropdown-item autotable-navigate hover-underline text-primary" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '">' . '#'.$q->id . '</a>')
            ->addColumn('is_future', fn ($q) => Carbon::parse($q->utc_date)->isFuture())
            ->addColumn('full_time', fn ($q) => $q->score ? ($q->score->home_scores_full_time . ' - ' . $q->score->away_scores_full_time) : '-')
            ->addColumn('half_time', fn ($q) => $q->score ? ($q->score->home_scores_half_time . ' - ' . $q->score->away_scores_half_time) : '-')
            ->addColumn('home_win_votes', $homeWinVotes)
            ->addColumn('draw_votes', $drawVotes)
            ->addColumn('away_win_votes', $awayWinVotes)
            ->addColumn('prediction', $prediction)
            ->addColumn('current_user_votes', $currentUserVotes)
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'ID']);

        if (request()->with_stats == 1)
            $results = $this->addGameStatistics($results);

        $results = $results->orderby('utc_date', request()->type == 'upcoming' ? 'asc' : 'desc');

        $results = $id ? $results->first() : $results->paginate();

        if ($without_response || request()->without_response) {
            return $results;
        }

        return response(['results' => $results]);
    }

    public function today()
    {
        request()->merge(['today' => true]);
        return $this->index();
    }

    public function yesterday()
    {
        request()->merge(['yesterday' => true]);
        return $this->index();
    }

    public function tomorrow()
    {
        request()->merge(['tomorrow' => true]);
        return $this->index();
    }

    public function year($year)
    {
        request()->merge(['year' => $year]);
        return $this->index();
    }

    public function yearMonth($year, $month)
    {
        request()->merge(['year' => $year, 'month' => $month]);
        return $this->index();
    }

    public function yearMonthDay($year, $month, $day)
    {
        request()->merge(['year' => $year, 'month' => $month, 'day' => $day]);
        return $this->index();
    }

    public function store(Request $request, $data)
    {
        $res = $this->autoSave($data);

        $action = 'created';
        if ($request->id)
            $action = 'updated';
        return response(['type' => 'success', 'message' => 'Status ' . $action . ' successfully', 'results' => $res]);
    }

    public function show($id)
    {
        return $this->index($id);
    }

    public function head2head($id)
    {
        $game = $this->model->find($id);

        $team_ids = [$game->home_team_id, $game->away_team_id];

        request()->merge(['team_ids' => $team_ids]);

        return $this->index();
    }

    public function vote($id, $data)
    {
        $user_id = auth()->id() ?? 0;
        $user_ip = request()->ip();

        $type = $data['type'];
        $vote = $data['vote'];
        GameVote::updateOrCreate(
            [
                'game_id' => $id,
                'user_id' => $user_id,
                'user_ip' => $user_ip,
            ],
            [
                'game_id' => $id,
                'user_id' => $user_id,
                'user_ip' => $user_ip,
                "{$type}" => $vote
            ]
        );

        return response(['type' => 'success', 'message' => 'Voted successfully', 'results' => $this->index($id, true)]);
    }

    // Add this new method to calculate and add game statistics
    private function addGameStatistics($results)
    {
        ini_set('max_execution_time', 60 * 10);

        return $results
            ->addColumn('stats', function ($matchData) {

                $home_team_id = $matchData['home_team_id'];

                $away_team_id = $matchData['away_team_id'];

                // Calculate the winner
                $winningSide = GameComposer::winningSide($matchData, true);

                // Calculate the total number of goals
                $goals = GameComposer::goals($matchData);

                // Calculate if both teams scored (gg)
                $bts = GameComposer::bts($matchData);

                $hda_target = $winningSide;

                $o15_target = ($goals > 1) ? 1 : 0;
                $o25_target = ($goals > 2) ? 1 : 0;
                $o35_target = ($goals > 3) ? 1 : 0;

                $bts_target = ($bts) ? 1 : 0;

                $cs_target = game_scores($matchData);

                $referees_ids = array_reduce($matchData->referees()->pluck('id')->toArray(), fn ($p, $c) => $p + $c, 0);

                $ignore_team_stats = false;

                // We only want matches with regular results
                if ($ignore_team_stats || ($winningSide != 0 && $winningSide != 1 && $winningSide != 2)) {
                    return [
                        'home_team_totals' => 0,
                        'home_team_wins' => 0,
                        'home_team_draws' => 0,
                        'home_team_loses' => 0,
                        'home_team_goals_for_avg' => 0,
                        'home_team_goals_against_avg' => 0,
                        'away_team_totals' => 0,
                        'away_team_wins' => 0,
                        'away_team_draws' => 0,
                        'away_team_loses' => 0,
                        'away_team_goals_for_avg' => 0,
                        'away_team_goals_against_avg' => 0,

                        'ht_home_team_totals' => 0,
                        'ht_home_team_wins' => 0,
                        'ht_home_team_draws' => 0,
                        'ht_home_team_loses' => 0,
                        'ht_home_team_goals_for_avg' => 0,
                        'ht_home_team_goals_against_avg' => 0,
                        'ht_away_team_totals' => 0,
                        'ht_away_team_wins' => 0,
                        'ht_away_team_draws' => 0,
                        'ht_away_team_loses' => 0,
                        'ht_away_team_goals_for_avg' => 0,
                        'ht_away_team_goals_against_avg' => 0,

                        'target' => false,
                        'hda_target' => $ignore_team_stats ? $hda_target : -1,
                        'ov15_target' => $ignore_team_stats ? $o15_target : -1,
                        'ov25_target' => $ignore_team_stats ? $o25_target : -1,
                        'ov35_target' => $ignore_team_stats ? $o35_target : -1,
                        'bts_target' => $ignore_team_stats ? $bts_target : -1,
                        'cs_target' => $ignore_team_stats ? $cs_target : -1,
                        'referees_ids' => $ignore_team_stats ? $referees_ids : -1,

                    ];
                }

                // Get home and away team matches and calculate statistics (replace with actual logic)

                $to_date = Carbon::parse($matchData->utc_date)->subDay()->format('Y-m-d');

                $teamsStats = $this->teamStats($to_date, $home_team_id, $away_team_id);
                $teamsStatsRecent = $this->teamStatsCurrentground($to_date, $home_team_id, $away_team_id);
                $teamsHead2HeadStats = $this->teamsHead2HeadStats($to_date, $home_team_id, $away_team_id, $matchData->id);

                return array_merge(
                    $teamsStats,
                    $teamsStatsRecent,
                    $teamsHead2HeadStats,
                    [

                        'target' => true,
                        'hda_target' => $hda_target,
                        'ov15_target' => $o15_target,
                        'ov25_target' => $o25_target,
                        'ov35_target' => $o35_target,
                        'bts_target' => $bts_target,
                        'cs_target' => $cs_target,
                        'referees_ids' => $referees_ids,
                    ]
                );
            });
    }

    private function teamStats($to_date, $home_team_id, $away_team_id)
    {
        request()->merge(['_to_date' => $to_date, '_per_page' => 20, '_without_response' => true]);

        $home_team_matches = array_reverse(json_decode(app(TeamController::class)->matches($home_team_id))->data);
        $away_team_matches = array_reverse(json_decode(app(TeamController::class)->matches($away_team_id))->data);

        $home_team_matches_with_stats = $this->calculateTeamStats($home_team_matches, $home_team_id);
        $away_team_matches_with_stats = $this->calculateTeamStats($away_team_matches, $away_team_id);

        return [
            'home_team_totals' => $home_team_matches_with_stats['totals'],
            'home_team_wins' => $home_team_matches_with_stats['teamWins'],
            'home_team_draws' => $home_team_matches_with_stats['draws'],
            'home_team_loses' => $home_team_matches_with_stats['teamLoses'],
            'home_team_goals_for_avg' => $home_team_matches_with_stats['goalsForAvg'],
            'home_team_goals_against_avg' => $home_team_matches_with_stats['goalsAgainstAvg'],
            'away_team_totals' => $away_team_matches_with_stats['totals'],
            'away_team_wins' => $away_team_matches_with_stats['teamWins'],
            'away_team_draws' => $away_team_matches_with_stats['draws'],
            'away_team_loses' => $away_team_matches_with_stats['teamLoses'],
            'away_team_goals_for_avg' => $away_team_matches_with_stats['goalsForAvg'],
            'away_team_goals_against_avg' => $away_team_matches_with_stats['goalsAgainstAvg'],
            'ht_home_team_totals' => $home_team_matches_with_stats['ht_totals'],
            'ht_home_team_wins' => $home_team_matches_with_stats['ht_teamWins'],
            'ht_home_team_draws' => $home_team_matches_with_stats['ht_draws'],
            'ht_home_team_loses' => $home_team_matches_with_stats['ht_teamLoses'],
            'ht_home_team_goals_for_avg' => $home_team_matches_with_stats['ht_goalsForAvg'],
            'ht_home_team_goals_against_avg' => $home_team_matches_with_stats['ht_goalsAgainstAvg'],
            'ht_away_team_totals' => $away_team_matches_with_stats['ht_totals'],
            'ht_away_team_wins' => $away_team_matches_with_stats['ht_teamWins'],
            'ht_away_team_draws' => $away_team_matches_with_stats['ht_draws'],
            'ht_away_team_loses' => $away_team_matches_with_stats['ht_teamLoses'],
            'ht_away_team_goals_for_avg' => $away_team_matches_with_stats['ht_goalsForAvg'],
            'ht_away_team_goals_against_avg' => $away_team_matches_with_stats['ht_goalsAgainstAvg'],

        ];
    }

    private function teamStatsCurrentground($to_date, $home_team_id, $away_team_id)
    {
        request()->merge(['_to_date' => $to_date, '_per_page' => 6, '_without_response' => true]);
        request()->merge(['_currentground' => 'home']);
        $home_team_matches = array_reverse(json_decode(app(TeamController::class)->matches($home_team_id))->data);
        request()->merge(['_currentground' => 'away']);
        $away_team_matches = array_reverse(json_decode(app(TeamController::class)->matches($away_team_id))->data);

        $home_team_matches_with_stats = $this->calculateTeamStats($home_team_matches, $home_team_id);
        $away_team_matches_with_stats = $this->calculateTeamStats($away_team_matches, $away_team_id);

        return [
            'current_ground_home_team_totals' => $home_team_matches_with_stats['totals'],
            'current_ground_home_team_wins' => $home_team_matches_with_stats['teamWins'],
            'current_ground_home_team_draws' => $home_team_matches_with_stats['draws'],
            'current_ground_home_team_loses' => $home_team_matches_with_stats['teamLoses'],
            'current_ground_home_team_goals_for_avg' => $home_team_matches_with_stats['goalsForAvg'],
            'current_ground_home_team_goals_against_avg' => $home_team_matches_with_stats['goalsAgainstAvg'],
            'current_ground_away_team_totals' => $away_team_matches_with_stats['totals'],
            'current_ground_away_team_wins' => $away_team_matches_with_stats['teamWins'],
            'current_ground_away_team_draws' => $away_team_matches_with_stats['draws'],
            'current_ground_away_team_loses' => $away_team_matches_with_stats['teamLoses'],
            'current_ground_away_team_goals_for_avg' => $away_team_matches_with_stats['goalsForAvg'],
            'current_ground_away_team_goals_against_avg' => $away_team_matches_with_stats['goalsAgainstAvg'],
            'current_ground_ht_home_team_totals' => $home_team_matches_with_stats['ht_totals'],
            'current_ground_ht_home_team_wins' => $home_team_matches_with_stats['ht_teamWins'],
            'current_ground_ht_home_team_draws' => $home_team_matches_with_stats['ht_draws'],
            'current_ground_ht_home_team_loses' => $home_team_matches_with_stats['ht_teamLoses'],
            'current_ground_ht_home_team_goals_for_avg' => $home_team_matches_with_stats['ht_goalsForAvg'],
            'current_ground_ht_home_team_goals_against_avg' => $home_team_matches_with_stats['ht_goalsAgainstAvg'],
            'current_ground_ht_away_team_totals' => $away_team_matches_with_stats['ht_totals'],
            'current_ground_ht_away_team_wins' => $away_team_matches_with_stats['ht_teamWins'],
            'current_ground_ht_away_team_draws' => $away_team_matches_with_stats['ht_draws'],
            'current_ground_ht_away_team_loses' => $away_team_matches_with_stats['ht_teamLoses'],
            'current_ground_ht_away_team_goals_for_avg' => $away_team_matches_with_stats['ht_goalsForAvg'],
            'current_ground_ht_away_team_goals_against_avg' => $away_team_matches_with_stats['ht_goalsAgainstAvg'],

        ];
    }

    private function teamsHead2HeadStats($to_date, $home_team_id, $away_team_id, $match_id)
    {

        request()->merge(['_to_date' => $to_date, '_per_page' => 6, '_without_response' => true]);
        request()->merge(['_currentground' => null, 'team_id' => null]);
        $matches = array_reverse(json_decode(app(TeamController::class)->head2head($match_id))->data);

        $home_team_matches_with_stats = $this->calculateTeamStats($matches, $home_team_id, 3);
        $away_team_matches_with_stats = $this->calculateTeamStats($matches, $away_team_id, 3);

        return [
            'h2h_home_team_totals' => $home_team_matches_with_stats['totals'],
            'h2h_home_team_wins' => $home_team_matches_with_stats['teamWins'],
            'h2h_home_team_draws' => $home_team_matches_with_stats['draws'],
            'h2h_home_team_loses' => $home_team_matches_with_stats['teamLoses'],
            'h2h_home_team_goals_for_avg' => $home_team_matches_with_stats['goalsForAvg'],
            'h2h_home_team_goals_against_avg' => $home_team_matches_with_stats['goalsAgainstAvg'],
            'h2h_away_team_totals' => $away_team_matches_with_stats['totals'],
            'h2h_away_team_wins' => $away_team_matches_with_stats['teamWins'],
            'h2h_away_team_draws' => $away_team_matches_with_stats['draws'],
            'h2h_away_team_loses' => $away_team_matches_with_stats['teamLoses'],
            'h2h_away_team_goals_for_avg' => $away_team_matches_with_stats['goalsForAvg'],
            'h2h_away_team_goals_against_avg' => $away_team_matches_with_stats['goalsAgainstAvg'],
            'h2h_ht_home_team_totals' => $home_team_matches_with_stats['ht_totals'],
            'h2h_ht_home_team_wins' => $home_team_matches_with_stats['ht_teamWins'],
            'h2h_ht_home_team_draws' => $home_team_matches_with_stats['ht_draws'],
            'h2h_ht_home_team_loses' => $home_team_matches_with_stats['ht_teamLoses'],
            'h2h_ht_home_team_goals_for_avg' => $home_team_matches_with_stats['ht_goalsForAvg'],
            'h2h_ht_home_team_goals_against_avg' => $home_team_matches_with_stats['ht_goalsAgainstAvg'],
            'h2h_ht_away_team_totals' => $away_team_matches_with_stats['ht_totals'],
            'h2h_ht_away_team_wins' => $away_team_matches_with_stats['ht_teamWins'],
            'h2h_ht_away_team_draws' => $away_team_matches_with_stats['ht_draws'],
            'h2h_ht_away_team_loses' => $away_team_matches_with_stats['ht_teamLoses'],
            'h2h_ht_away_team_goals_for_avg' => $away_team_matches_with_stats['ht_goalsForAvg'],
            'h2h_ht_away_team_goals_against_avg' => $away_team_matches_with_stats['ht_goalsAgainstAvg'],

        ];
    }

    function calculateTeamStats($teamGames, $teamId, $increment = 1)
    {
        $totals = 0;
        $teamWins = 0;
        $draws = 0;
        $teamLoses = 0;
        $goalFor = 0;
        $goalsAgainst = 0;
        $goalsForAvg = 0;
        $goalsAgainstAvg = 0;

        // Half time
        $ht_totals = 0;
        $ht_teamWins = 0;
        $ht_draws = 0;
        $ht_teamLoses = 0;
        $ht_goalFor = 0;
        $ht_goalsAgainst = 0;
        $ht_goalsForAvg = 0;
        $ht_goalsAgainstAvg = 0;

        if (!empty($teamGames)) {
            foreach ($teamGames as $game) {

                $hasResults = GameComposer::hasResults($game);

                if ($hasResults) {
                    $totals += 1;
                    $ht_totals += 1;

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

                    // for ht
                    $winningSide = GameComposer::winningSideHT($game, true);
                    if ($winningSide === 1) {
                        $ht_draws += $increment;
                    } elseif ($winningSide === 0 || $winningSide === 2) {
                        $winnerId = GameComposer::winnerId($game);
                        if ($winnerId == $teamId) {
                            $ht_teamWins += $increment;
                        } else {
                            $ht_teamLoses += $increment;
                        }
                    }

                    // Get goals for and goals against
                    $goalFor += (GameComposer::getScores($game, $teamId) * $increment);
                    $goalsAgainst += (GameComposer::getScores($game, $teamId, true) * $increment);
                    $ht_goalFor += (GameComposer::getScoresHT($game, $teamId) * $increment);
                    $ht_goalsAgainst += (GameComposer::getScoresHT($game, $teamId, true) * $increment);
                }
            }

            // Calculate averages
            $goalsForAvg = $totals > 0 ? round($goalFor / $totals, 2) : 0;
            $goalsAgainstAvg = $totals > 0 ? round($goalsAgainst / $totals, 2) : 0;
            // averages for ht
            $ht_goalsForAvg = $totals > 0 ? round($ht_goalFor / $ht_totals, 2) : 0;
            $ht_goalsAgainstAvg = $totals > 0 ? round($ht_goalsAgainst / $ht_totals, 2) : 0;
        }

        return [
            'totals' => $totals,
            'teamWins' => $teamWins,
            'draws' => $draws,
            'teamLoses' => $teamLoses,
            'goalFor' => $goalFor,
            'goalsAgainst' => $goalsAgainst,
            'goalsForAvg' => $goalsForAvg,
            'goalsAgainstAvg' => $goalsAgainstAvg,

            'ht_totals' => $ht_totals,
            'ht_teamWins' => $ht_teamWins,
            'ht_draws' => $ht_draws,
            'ht_teamLoses' => $ht_teamLoses,
            'ht_goalFor' => $ht_goalFor,
            'ht_goalsAgainst' => $ht_goalsAgainst,
            'ht_goalsForAvg' => $ht_goalsForAvg,
            'ht_goalsAgainstAvg' => $ht_goalsAgainstAvg,
        ];
    }
}
