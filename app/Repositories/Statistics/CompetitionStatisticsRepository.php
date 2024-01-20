<?php

namespace App\Repositories\Statistics;

use App\Models\Competition;
use App\Models\CompetitionStatistic;
use App\Models\CompetitionStatistics;
use App\Models\Game;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\Game\GameRepositoryInterface;
use App\Repositories\GameComposer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CompetitionStatisticsRepository implements CompetitionStatisticsRepositoryInterface
{

    use CommonRepoActions;

    function __construct(
        protected CompetitionStatistics $model,
        protected GameRepositoryInterface $gameRepositoryInterface
    ) {
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

            $results->full_time_home_wins_percentage = number_format($results->full_time_home_wins / $counts * 100, 0, '');
            $results->full_time_draws_percentage = number_format($results->full_time_draws / $counts * 100, 0, '');
            $results->full_time_away_wins_percentage = number_format($results->full_time_away_wins / $counts * 100, 0, '');

            $results->half_time_home_wins_percentage = number_format($results->half_time_home_wins / $counts * 100, 0, '');
            $results->half_time_draws_percentage = number_format($results->half_time_draws / $counts * 100, 0, '');
            $results->half_time_away_wins_percentage = number_format($results->half_time_away_wins / $counts * 100, 0, '');

            $results->gg_percentage = number_format($results->gg / $counts * 100, 0, '');
            $results->ng_percentage = number_format($results->ng / $counts * 100, 0, '');

            $results->over15_percentage = number_format($results->over15 / $counts * 100, 0, '');
            $results->under15_percentage = number_format($results->under15 / $counts * 100, 0, '');

            $results->over25_percentage = number_format($results->over25 / $counts * 100, 0, '');
            $results->under25_percentage = number_format($results->under25 / $counts * 100, 0, '');

            $results->over35_percentage = number_format($results->over35 / $counts * 100, 0, '');
            $results->under35_percentage = number_format($results->under35 / $counts * 100, 0, '');
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
        ]);

        $games = $this->gameRepositoryInterface->index(null, true);

        $counts = 0;

        $full_time_home_wins_counts = 0;
        $full_time_draws_counts = 0;
        $full_time_away_wins_counts = 0;
        $half_time_home_wins_counts = 0;
        $half_time_draws_counts = 0;
        $half_time_away_wins_counts = 0;

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

            if (!$hasResults) return;

            $counts++;

            $winningSide = GameComposer::winningSide($game, true);

            if ($winningSide === 0) {
                $full_time_home_wins_counts++;
            } elseif ($winningSide === 1) {
                $full_time_draws_counts++;
            } elseif ($winningSide === 2) {
                $full_time_away_wins_counts++;
            }

            $winningSide = GameComposer::winningSideHT($game, true);

            if ($winningSide === 0) {
                $half_time_home_wins_counts++;
            } elseif ($winningSide === 1) {
                $half_time_draws_counts++;
            } elseif ($winningSide === 2) {
                $half_time_away_wins_counts++;
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
                        'half_time_home_wins' => $half_time_home_wins_counts,
                        'half_time_draws' => $half_time_draws_counts,
                        'half_time_away_wins' => $half_time_away_wins_counts,
                        'full_time_home_wins' => $full_time_home_wins_counts,
                        'full_time_draws' => $full_time_draws_counts,
                        'full_time_away_wins' => $full_time_away_wins_counts,
                        'gg' => $gg_counts,
                        'ng' => $ng_counts,
                        'over15' => $over15_counts,
                        'under15' => $under15_counts,
                        'over25' => $over25_counts,
                        'under25' => $under25_counts,
                        'over35' => $over35_counts,
                        'under35' => $under35_counts,
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
                        'half_time_home_wins' => $half_time_home_wins_counts,
                        'half_time_draws' => $half_time_draws_counts,
                        'half_time_away_wins' => $half_time_away_wins_counts,
                        'full_time_home_wins' => $full_time_home_wins_counts,
                        'full_time_draws' => $full_time_draws_counts,
                        'full_time_away_wins' => $full_time_away_wins_counts,
                        'gg' => $gg_counts,
                        'ng' => $ng_counts,
                        'over15' => $over15_counts,
                        'under15' => $under15_counts,
                        'over25' => $over25_counts,
                        'under25' => $under25_counts,
                        'over35' => $over35_counts,
                        'under35' => $under35_counts,
                    ]
                );
            }
        }

        Competition::find($competition_id)->update(['stats_last_done' => now()]);

        $arr = ['message' => 'Successfully done stats.', 'results' => ['updated' => $counts]];
        if (request()->without_response) return $arr;
        return response($arr);
    }
}
