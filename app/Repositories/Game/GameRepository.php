<?php

namespace App\Repositories\Game;

use App\Http\Controllers\Admin\Teams\View\TeamController;
use App\Models\Game;
use App\Models\GamePredictionType;
use App\Models\GameVote;
use App\Repositories\CommonRepoActions;
use App\Repositories\GameComposer;
use App\Repositories\SearchRepo;
use App\Repositories\Team\TeamRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameRepository implements GameRepositoryInterface
{

    use CommonRepoActions;

    protected $sourceContext;

    function __construct(protected Game $model, protected TeamRepositoryInterface $teamRepositoryInterface)
    {
    }

    public function index($id = null, $without_response = null)
    {
        $games = $this->applyGameFilters($id);


        $homeWinVotes = function ($q) {
            return $q->votes->where('winner', 'home')->count();
        };

        $drawVotes = function ($q) {
            return $q->votes->where('winner', 'draw')->count();
        };

        $awayWinVotes = function ($q) {
            return $q->votes->where('winner', 'away')->count();
        };

        $uri = '/admin/matches/';
        $results = SearchRepo::of($games, ['id', 'name'])
            ->addColumn('ID', fn ($q) => '<a class="dropdown-item autotable-navigate hover-underline text-decoration-underline" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '">' . '#' . $q->id . '</a>')
            ->addColumn('is_future', fn ($q) => Carbon::parse($q->utc_date)->isFuture())
            ->addColumn('full_time', fn ($q) => $q->score ? ($q->score->home_scores_full_time . ' - ' . $q->score->away_scores_full_time) : '-')
            ->addColumn('half_time', fn ($q) => $q->score ? ($q->score->home_scores_half_time . ' - ' . $q->score->away_scores_half_time) : '-')
            ->addColumn('home_win_votes', $homeWinVotes)
            ->addColumn('draw_votes', $drawVotes)
            ->addColumn('away_win_votes', $awayWinVotes)
            ->addColumn('formatted_prediction', fn ($q) => $this->format_pred(clone $q))
            ->addColumnWhen(request()->break_preds, 'Game', fn ($q) => '<a class="dropdown-item autotable-navigate hover-underline text-decoration-underline" data-id="' . $q->id . '" href="' . $uri . 'view/' . $q->id . '">' . $q->home_team->name . ' vs ' . $q->away_team->name . '</a>', 'cs')
            ->addColumnWhen(request()->break_preds, '1X2', fn ($q) => $this->format1X2(clone $q))
            ->addColumnWhen(request()->break_preds, 'Pick', fn ($q) => $this->formatHda(clone $q))
            ->addColumnWhen(request()->break_preds, 'BTS', fn ($q) => $this->formatBTS(clone $q))
            ->addColumnWhen(request()->break_preds, 'Over25', fn ($q) => $this->formatGoals(clone $q))
            ->addColumnWhen(request()->break_preds, 'CS', fn ($q) => $this->formatCS(clone $q))
            ->addColumnWhen(request()->break_preds, 'Halftime', fn ($q) => $this->formatHTScores(clone $q))
            ->addColumnWhen(request()->break_preds, 'Fulltime', fn ($q) => $this->formatFTScores(clone $q))
            ->addColumn('current_user_votes', fn ($q) => $this->currentUserVotes($q))
            ->addColumn('Created_at', 'Created_at')
            ->addColumn('Status', 'Status')
            ->addActionColumn('action', $uri, 'native')
            ->htmls(['Status', 'ID', 'Game', '1X2', 'Pick', 'BTS', 'Over25', 'CS', 'Halftime', 'Fulltime']);

        if (request()->with_stats == 1)
            $results = $this->addGameStatistics($results);

        if (!request()->order_by)
            $results = $results->orderby('utc_date', request()->type == 'upcoming' ? 'asc' : 'desc');

        $results = $id ? $results->first() : $results->paginate(request()->per_page ?? 50);

        if ($without_response || request()->without_response) {
            return $results;
        }

        return response(['results' => $results]);
    }

    private function applyGameFilters($id)
    {

        if (request()->prediction_type) {
            $prediction_type = GamePredictionType::where('name', request()->prediction_type)->first();
            if ($prediction_type) {
                preg_match_all('/\d+/', $prediction_type->name, $matches);
                $result = $matches[0];

                request()->merge([
                    'history_limit_per_match' => $result[0],
                    'current_ground_limit_per_match' => $result[1],
                    'h2h_limit_per_match' => $result[2],
                ]);
            }
        }

        $type_cb = function ($q) {
            $to_date = request()->to_date ?? Carbon::now();
            request()->type == 'past' ? $q->where('utc_date', '<', $to_date) : (request()->type == 'upcoming' ? $q->where('utc_date', '>=', Carbon::now()) :  $q);
        };

        $games = $this->model::with(['competition' => fn ($q) => $q->with(['country', 'currentSeason']), 'home_team', 'away_team', 'score', 'votes', 'referees', 'prediction'])
            ->when(request()->country_id, fn ($q) => $q->where('country_id', request()->country_id))
            ->when(request()->competition_id, fn ($q) => $q->where('competition_id', request()->competition_id))
            ->when(request()->season_id, fn ($q) => $q->where('season_id', request()->season_id))

            ->when(request()->team_id, fn ($q) => $q->where(fn ($q) => $q->where('home_team_id', request()->team_id)->orWhere('away_team_id', request()->team_id)))
            ->when(request()->team_ids, fn ($q) => $this->teamsMatch($q))

            ->when(request()->currentground, fn ($q) => request()->currentground == 'home' ? $q->where('home_team_id', request()->team_id) : (request()->currentground == 'away' ? $q->where('away_team_id', request()->team_id) :  $q))
            ->when(request()->yesterday, fn ($q) => $q->whereDate('utc_date', Carbon::yesterday()))
            ->when(request()->today, fn ($q) => $q->whereDate('utc_date', Carbon::today()))
            ->when(request()->tomorrow, fn ($q) => $q->whereDate('utc_date', Carbon::tomorrow()))
            ->when(request()->year, fn ($q) => $q->whereYear('utc_date', request()->year))
            ->when(request()->from_date, fn ($q) => $q->whereDate('utc_date', '>=', Carbon::parse(request()->from_date)->format('Y-m-d')))
            ->when(request()->to_date, fn ($q) => $q->whereDate('utc_date', '<=', Carbon::parse(request()->to_date)->format('Y-m-d')))
            ->when(request()->date, fn ($q) => $q->whereDate('utc_date', '=', Carbon::parse(request()->date)->format('Y-m-d')))
            ->when(!request()->date && request()->type, $type_cb)
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

        return $games;
    }

    private function currentUserVotes($q)
    {
        return !!$q->votes->where(function ($q) {
            return $q->where('user_id', auth()->id())->orWhere('user_ip', request()->ip());
        })->whereNotNull('winner')->first();
    }

    private function teamsMatch($q)
    {

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
    }

    private function single_pred($q, $col = null)
    {

        if ($q->prediction) {
            $pred = $this->format_pred($q->prediction);

            if ($col == 'hda_proba')
                return $pred->home_win_proba . '%, ' . $pred->draw_proba . '%, ' . $pred->away_win_proba . '%';
            if ($col == 'hda')
                return $pred->Hda;
            if ($col == 'bts')
                return $pred->Bts;
            if ($col == 'over25')
                return $pred->Over25;
            if ($col == 'cs')
                return $pred->Cs;

            return $pred;
        }

        return '-';
    }

    private function format_pred($pred)
    {
        $cs = array_search($pred->cs, scores());
        $pred->Hda = $pred->hda == 0 ? '1' : ($pred->hda == 1 ? 'X' : '2');
        $pred->Bts = $pred->bts == 1 ? 'YES' : 'NO';
        $pred->Over25 = $pred->over25 == 1 ? 'OV' : 'UN';
        $pred->Cs = $cs;
        return $pred;
    }

    private function format1X2($q)
    {
        $class = 'border-start text-dark';

        return '<div class="border-4 ps-1 text-nowrap ' . $class . ' d-inline-block">' . $this->single_pred(clone $q, 'hda_proba') . '</div>';
    }

    private function formatHda($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::winningSide($q, true);

        $class = 'bg-light-blue text-dark';

        $pred = $q->prediction;
        if ($pred && $has_res) {
            if ($pred->hda == $res) {
                $class = 'border-bottom bg-success text-white';
            } elseif ($pred) {
                $class = 'border-bottom border-danger text-danger';
            }
        }

        return '<div class="rounded-circle border p-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $this->single_pred(clone $q, 'hda') . '</div>';
    }

    private function formatBTS($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::bts($q, true);

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->prediction;
        if ($pred && $has_res) {
            if ($pred->bts == $res) {
                $class = 'border-bottom border-success';
            } elseif ($pred) {
                $class = 'border-bottom border-danger ';
            }
        }

        return '<div class="border-2 py-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $this->single_pred(clone $q, 'bts') . '</div>';
    }

    private function formatGoals($q)
    {
        $has_res = GameComposer::hasResults($q);
        $res = GameComposer::goals($q, true);

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->prediction;
        if ($pred && $has_res) {
            if ($pred->over25 && $res > 2) {
                $class = 'border-bottom border-success';
            } elseif (!$pred->over25 && $res <= 2) {
                $class = 'border-bottom border-success';
            } elseif ($pred) {
                $class = 'border-bottom border-danger';
            }
        }

        return '<div class="border-2 py-1 ' . $class . ' d-inline-block text-center results-icon-md">' . $this->single_pred(clone $q, 'over25') . '</div>';
    }

    private function formatCS($q)
    {

        $has_res = GameComposer::hasResults($q);

        $class = 'border-bottom-light-blue text-dark';

        $pred = $q->prediction;
        if ($pred && $has_res) {
            $res = GameComposer::cs($q);

            if ($res) {
                $class = 'border-bottom border-success';
            }
        }

        return '<div class="border-2 py-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $this->single_pred(clone $q, 'cs') . '</div>';
    }

    private function formatHTScores($q)
    {

        $class = 'border-start text-dark';

        return '<div class="border-4 p-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $q->half_time . '</div>';
    }

    private function formatFTScores($q)
    {

        $class = 'border-start text-dark';

        return '<div class="border-4 p-1 text-nowrap ' . $class . ' d-inline-block text-center results-icon-md">' . $q->full_time . '</div>';
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

        Log::info('history_limits: ', [request()->history_limit_per_match, request()->current_ground_limit_per_match, request()->h2h_limit_per_match]);

        return $results
            ->addColumn('stats', function ($matchData) {

                $home_team_id = $matchData['home_team_id'];

                $away_team_id = $matchData['away_team_id'];

                // Calculate the winner
                $winningSide = GameComposer::winningSide($matchData, true);

                $hda_target = $winningSide;

                // Calculate if both teams scored (bts)
                $bts = GameComposer::bts($matchData);

                $bts_target = $bts ? 1 : 0;

                // Calculate the total number of goals
                $goals = GameComposer::goals($matchData);

                $over15_target = ($goals > 1) ? 1 : 0;
                $over25_target = ($goals > 2) ? 1 : 0;
                $over35_target = ($goals > 3) ? 1 : 0;


                $cs_target = game_scores($matchData->score);

                $referees_ids = array_reduce($matchData->referees()->pluck('id')->toArray(), fn ($p, $c) => $p + $c, 0);

                // Get home and away team matches and calculate statistics (replace with actual logic)

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

                return array_merge(
                    $teamsStats,
                    $teamsStatsRecent,
                    $teamsHead2HeadStats,
                    [

                        'has_results' => GameComposer::hasResults($matchData),
                        'hda_target' => $hda_target,
                        'over15_target' => $over15_target,
                        'over25_target' => $over25_target,
                        'over35_target' => $over35_target,
                        'bts_target' => $bts_target,
                        'cs_target' => $cs_target,
                        'referees_ids' => $referees_ids,
                    ],
                    $team_lp_home_team_details,
                    $team_lp_away_team_details,
                );
            });
    }

    private function teamStats($to_date, $home_team_id, $away_team_id)
    {

        $per_page = request()->history_limit_per_match ?? 10;

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

        $home_team_matches = app(TeamController::class)->matches($home_team_id)['data'];
        if (count($home_team_matches) < $per_page) return null;

        $away_team_matches = app(TeamController::class)->matches($away_team_id)['data'];
        if (count($away_team_matches) < $per_page) return null;

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

        $per_page = 4;
        if (request()->current_ground_limit_per_match)
            $per_page = request()->current_ground_limit_per_match;
        $per_page = $per_page < 4 ? 4 : $per_page;

        $all_params = [
            'team_id' => null,
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

        $home_team_matches = app(TeamController::class)->matches($home_team_id)['data'];
        if (count($home_team_matches) < $per_page) return null;

        $all_params['currentground'] = 'away';
        request()->merge(['all_params' => $all_params]);

        $away_team_matches = app(TeamController::class)->matches($away_team_id)['data'];
        if (count($away_team_matches) < $per_page) return null;

        $home_team_matches_with_stats = $this->calculateTeamStats($home_team_matches, $home_team_id);
        $away_team_matches_with_stats = $this->calculateTeamStats($away_team_matches, $away_team_id);

        return [
            'current_ground_home_team_totals' => $home_team_matches_with_stats['totals'],
            'current_ground_home_team_wins' => $home_team_matches_with_stats['teamWins'],
            'current_ground_home_team_draws' => $home_team_matches_with_stats['draws'],
            'current_ground_home_team_loses' => $home_team_matches_with_stats['teamLoses'],
            'current_ground_home_team_goals_for' => $home_team_matches_with_stats['goalsFor'],
            'current_ground_home_team_goals_for_avg' => $home_team_matches_with_stats['goalsForAvg'],
            'current_ground_home_team_goals_against' => $home_team_matches_with_stats['goalsAgainst'],
            'current_ground_home_team_goals_against_avg' => $home_team_matches_with_stats['goalsAgainstAvg'],
            'current_ground_home_team_bts_games' => $home_team_matches_with_stats['bts_games'],
            'current_ground_home_team_over15_games' => $home_team_matches_with_stats['over15_games'],
            'current_ground_home_team_over25_games' => $home_team_matches_with_stats['over25_games'],
            'current_ground_home_team_over35_games' => $home_team_matches_with_stats['over35_games'],

            'current_ground_away_team_totals' => $away_team_matches_with_stats['totals'],
            'current_ground_away_team_wins' => $away_team_matches_with_stats['teamWins'],
            'current_ground_away_team_draws' => $away_team_matches_with_stats['draws'],
            'current_ground_away_team_loses' => $away_team_matches_with_stats['teamLoses'],
            'current_ground_away_team_goals_for' => $away_team_matches_with_stats['goalsFor'],
            'current_ground_away_team_goals_for_avg' => $away_team_matches_with_stats['goalsForAvg'],
            'current_ground_away_team_goals_against' => $away_team_matches_with_stats['goalsAgainst'],
            'current_ground_away_team_goals_against_avg' => $away_team_matches_with_stats['goalsAgainstAvg'],
            'current_ground_away_team_bts_games' => $away_team_matches_with_stats['bts_games'],
            'current_ground_away_team_over15_games' => $away_team_matches_with_stats['over15_games'],
            'current_ground_away_team_over25_games' => $away_team_matches_with_stats['over25_games'],
            'current_ground_away_team_over35_games' => $away_team_matches_with_stats['over35_games'],

            'current_ground_ht_home_team_totals' => $home_team_matches_with_stats['ht_totals'],
            'current_ground_ht_home_team_wins' => $home_team_matches_with_stats['ht_teamWins'],
            'current_ground_ht_home_team_draws' => $home_team_matches_with_stats['ht_draws'],
            'current_ground_ht_home_team_loses' => $home_team_matches_with_stats['ht_teamLoses'],
            'current_ground_ht_home_team_goals_for' => $home_team_matches_with_stats['ht_goalsFor'],
            'current_ground_ht_home_team_goals_for_avg' => $home_team_matches_with_stats['ht_goalsForAvg'],
            'current_ground_ht_home_team_goals_against' => $home_team_matches_with_stats['ht_goalsAgainst'],
            'current_ground_ht_home_team_goals_against_avg' => $home_team_matches_with_stats['ht_goalsAgainstAvg'],
            'current_ground_ht_home_team_bts_games' => $home_team_matches_with_stats['ht_bts_games'],
            'current_ground_ht_home_team_over15_games' => $home_team_matches_with_stats['ht_over15_games'],
            'current_ground_ht_home_team_over25_games' => $home_team_matches_with_stats['ht_over25_games'],
            'current_ground_ht_home_team_over35_games' => $home_team_matches_with_stats['ht_over35_games'],

            'current_ground_ht_away_team_totals' => $away_team_matches_with_stats['ht_totals'],
            'current_ground_ht_away_team_wins' => $away_team_matches_with_stats['ht_teamWins'],
            'current_ground_ht_away_team_draws' => $away_team_matches_with_stats['ht_draws'],
            'current_ground_ht_away_team_loses' => $away_team_matches_with_stats['ht_teamLoses'],
            'current_ground_ht_away_team_goals_for' => $away_team_matches_with_stats['ht_goalsFor'],
            'current_ground_ht_away_team_goals_for_avg' => $away_team_matches_with_stats['ht_goalsForAvg'],
            'current_ground_ht_away_team_goals_against' => $away_team_matches_with_stats['ht_goalsAgainst'],
            'current_ground_ht_away_team_goals_against_avg' => $away_team_matches_with_stats['ht_goalsAgainstAvg'],
            'current_ground_ht_away_team_bts_games' => $away_team_matches_with_stats['ht_bts_games'],
            'current_ground_ht_away_team_over15_games' => $away_team_matches_with_stats['ht_over15_games'],
            'current_ground_ht_away_team_over25_games' => $away_team_matches_with_stats['ht_over25_games'],
            'current_ground_ht_away_team_over35_games' => $away_team_matches_with_stats['ht_over35_games'],

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

            // Calculate averages
            $goalsForAvg = $totals > 0 ? round($goalsFor / $totals, 2) : 0;
            $goalsAgainstAvg = $totals > 0 ? round($goalsAgainst / $totals, 2) : 0;
            // averages for ht
            $ht_goalsForAvg = $totals > 0 ? round($ht_goalsFor / $ht_totals, 2) : 0;
            $ht_goalsAgainstAvg = $totals > 0 ? round($ht_goalsAgainst / $ht_totals, 2) : 0;
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
}
