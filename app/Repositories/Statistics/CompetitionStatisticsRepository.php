<?php

namespace App\Repositories\Statistics;

use App\Models\Competition;
use App\Models\CompetitionStatistic;
use App\Models\CompetitionStatistics;
use App\Models\Game;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\Game\GameRepository;
use App\Repositories\Game\GameRepositoryInterface;
use App\Repositories\GameComposer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CompetitionStatisticsRepository implements CompetitionStatisticsRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected CompetitionStatistics $model)
    {
    }

    function index()
    {

        sleep(0);

        $results = $this->model->where('competition_id', request()->competition_id)
            ->when(request()->season_id, fn ($q) => $q->where('season_id', request()->season_id))
            ->when(request()->from_date, fn ($q) => $q->whereDate('date', '>=', Carbon::parse(request()->from_date)->format('Y-m-d')))
            ->when(request()->to_date, fn ($q) => $q->whereDate('date', '<=', Carbon::parse(request()->to_date)->format('Y-m-d')))
            ->first();

        $counts = $results->counts ?? 0;

        if ($counts > 0) {

            $results->ft_home_wins_percentage = number_format($results->ft_home_wins / $counts * 100, 0, '');
            $results->ft_draws_percentage = number_format($results->ft_draws / $counts * 100, 0, '');
            $results->ft_away_wins_percentage = number_format($results->ft_away_wins / $counts * 100, 0, '');

            $results->ht_home_wins_percentage = number_format($results->ht_home_wins / $counts * 100, 0, '');
            $results->ht_draws_percentage = number_format($results->ht_draws / $counts * 100, 0, '');
            $results->ht_away_wins_percentage = number_format($results->ht_away_wins / $counts * 100, 0, '');

            $results->ft_gg_percentage = number_format($results->gg / $counts * 100, 0, '');
            $results->ft_ng_percentage = number_format($results->ng / $counts * 100, 0, '');

            $results->ft_over15_percentage = number_format($results->over15 / $counts * 100, 0, '');
            $results->ft_under15_percentage = number_format($results->under15 / $counts * 100, 0, '');

            $results->ft_over25_percentage = number_format($results->over25 / $counts * 100, 0, '');
            $results->ft_under25_percentage = number_format($results->under25 / $counts * 100, 0, '');

            $results->ft_over35_percentage = number_format($results->over35 / $counts * 100, 0, '');
            $results->ft_under35_percentage = number_format($results->under35 / $counts * 100, 0, '');
        }

        return response(['results' => $results]);
    }

    public function store()
    {

        $competition_id = request()->competition_id;
        $season = Season::find(request()->season_id);
        $season_id = $season->id ?? 0;

        request()->merge([
            'per_page' => 700, 'order_by' => 'utc_date', 'order_direction' => 'asc',
            'without_response' => true,
            'is_competition_stats' => true,
        ]);

        $games = (new GameRepository(new Game()))->index(null, true);

        $counts = 0;

        $ft_home_wins_counts = 0;
        $ft_draws_counts = 0;
        $ft_away_wins_counts = 0;
        $ht_home_wins_counts = 0;
        $ht_draws_counts = 0;
        $ht_away_wins_counts = 0;

        $gg_counts = 0;
        $ng_counts = 0;

        $over15_counts = 0;
        $under15_counts = 0;
        $over25_counts = 0;
        $under25_counts = 0;
        $over35_counts = 0;
        $under35_counts = 0;

        $games = $games['results']['data'];
        $ct = count($games);

        // echo "Total games for competition #{$competition_id}: $ct\n\n";

        $unique_dates_counts = count(array_unique(
            array_map(fn ($c) => Carbon::parse($c)->format('Y-m-d'), array_column($games, 'utc_date'))
        ));
        // Log::info("Unique date counts: {$unique_dates_counts}");

        $matchday = 0;
        foreach ($games as $game) {
            $id = $game['id'];
            $date = $game['utc_date'];
            $score = $game['score'];

            $matchday = $game['matchday'];

            // echo "Date: {$date}, Game:{$id}\n";

            if (!$score) {
                // echo "No scores.\n";
                continue;
            }

            $hasResults = GameComposer::hasResults($game);

            if (!$hasResults) continue;

            $counts++;

            $winningSide = GameComposer::winningSide($game, true);

            if ($winningSide === 0) {
                $ft_home_wins_counts++;
            } elseif ($winningSide === 1) {
                $ft_draws_counts++;
            } elseif ($winningSide === 2) {
                $ft_away_wins_counts++;
            }

            $winningSide = GameComposer::winningSideHT($game, true);

            if ($winningSide === 0) {
                $ht_home_wins_counts++;
            } elseif ($winningSide === 1) {
                $ht_draws_counts++;
            } elseif ($winningSide === 2) {
                $ht_away_wins_counts++;
            }

            $bts = GameComposer::bts($game, true);

            if ($bts) {
                $gg_counts++;
            } else {
                $ng_counts++;
            }

            $goals = GameComposer::goals($game, true);

            if ($goals > 1) {
                $over15_counts++;
            } else {
                $under15_counts++;
            }
            if ($goals > 2) {
                $over25_counts++;
            } else {
                $under25_counts++;
            }
            if ($goals > 3) {
                $over35_counts++;
            } else {
                $under35_counts++;
            }
        }

        if ($counts > 0) {
            if ($season_id && (!request()->date || $unique_dates_counts > 1)) {
                CompetitionStatistic::updateOrCreate(
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                    ],
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                        'counts' => $counts,
                        'ht_home_wins' => $ht_home_wins_counts,
                        'ht_draws' => $ht_draws_counts,
                        'ht_away_wins' => $ht_away_wins_counts,
                        'ft_home_wins' => $ft_home_wins_counts,
                        'ft_draws' => $ft_draws_counts,
                        'ft_away_wins' => $ft_away_wins_counts,
                        'ft_gg' => $gg_counts,
                        'ft_ng' => $ng_counts,
                        'ft_over15' => $over15_counts,
                        'ft_under15' => $under15_counts,
                        'ft_over25' => $over25_counts,
                        'ft_under25' => $under25_counts,
                        'ft_over35' => $over35_counts,
                        'ft_under35' => $under35_counts,
                    ]
                );
            } else {

                $date = Carbon::parse(request()->date)->format('Y-m-d');

                if (!$season_id) {

                    $game = Game::find($game['id']);
                    $season_id = $game->season_id ?? 0;
                }

                CompetitionStatistic::updateOrCreate(
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                        'date' => $date,
                        'matchday' => $matchday,
                    ],
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                        'date' => $date,
                        'matchday' => $matchday,
                        'counts' => $counts,
                        'ht_home_wins' => $ht_home_wins_counts,
                        'ht_draws' => $ht_draws_counts,
                        'ht_away_wins' => $ht_away_wins_counts,
                        'ft_home_wins' => $ft_home_wins_counts,
                        'ft_draws' => $ft_draws_counts,
                        'ft_away_wins' => $ft_away_wins_counts,
                        'ft_gg' => $gg_counts,
                        'ft_ng' => $ng_counts,
                        'ft_over15' => $over15_counts,
                        'ft_under15' => $under15_counts,
                        'ft_over25' => $over25_counts,
                        'ft_under25' => $under25_counts,
                        'ft_over35' => $over35_counts,
                        'ft_under35' => $under35_counts,
                    ]
                );
            }
        }

        Competition::find($competition_id)->update(['stats_last_done' => now()]);

        $arr = ['message' => 'Total matches ' . $ct . ', successfully done stats, (updated ' . $counts . ').', 'results' => ['updated' => $counts]];
        if (request()->without_response) return $arr;
        return response($arr);
    }
}
