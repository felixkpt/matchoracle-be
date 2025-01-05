<?php

namespace App\Repositories\Statistics;

use App\Models\Competition;
use App\Models\CompetitionStatistic;
use App\Models\CompetitionStatistics;
use App\Models\Game;
use App\Models\Season;
use App\Repositories\CommonRepoActions;
use App\Repositories\Game\GameRepository;
use App\Repositories\GameComposer;
use Illuminate\Support\Carbon;

class CompetitionStatisticsRepository implements CompetitionStatisticsRepositoryInterface
{

    use CommonRepoActions;

    function __construct(protected CompetitionStatistics $model) {}

    function index()
    {

        $results = $this->model
            ->when(request()->competition_id, fn($q) => $q->where('competition_id', request()->competition_id))
            ->when(request()->season_id, fn($q) => $q->where('season_id', request()->season_id))
            ->when(request()->from_date, fn($q) => $q->whereDate('date', '>=', Carbon::parse(request()->from_date)->format('Y-m-d')))
            ->when(request()->to_date, fn($q) => $q->whereDate('date', '<=', Carbon::parse(request()->to_date)->format('Y-m-d')))
            ->get()->toArray();

        $aggregatedResults = [];
        $season_counts = 0;
        foreach ($results as $seasonResults) {
            $ft_counts = $seasonResults['ft_counts'] ?? 0;
            if ($ft_counts > 0) {
                $season_counts++;
                // Aggregate raw counts and percentages
                $this->updateAggregatedResults($aggregatedResults, $seasonResults, 'ft');
            }

            $ht_counts = $seasonResults['ht_counts'] ?? 0;
            if ($ht_counts > 0) {
                $this->updateAggregatedResults($aggregatedResults, $seasonResults, 'ht');
            }
        }


        if (count($aggregatedResults)) {
            $results = $results[0];

            // Normalize percentages based on total counts
            $ft_counts = $aggregatedResults['ft_counts'];
            $ht_counts = $aggregatedResults['ht_counts'];
            $results['ft_counts'] = $ft_counts;
            $results['ht_counts'] = $ht_counts;
            foreach ($aggregatedResults as $key => &$value) {
                if (str_starts_with($key, 'ft_')) {
                    $results["{$key}_percentage"] = (int) round(($value / $ft_counts) * 100);
                } elseif (str_starts_with($key, 'ht_')) {
                    $results["{$key}_percentage"] = (int) round(($value / $ht_counts) * 100);
                }

                $results[$key] = $value;
            }

            $results['season_counts'] = 1;
            if ($season_counts > 1) {
                $results['season_id'] = null;
                $results['season_counts'] = $season_counts;
            }
        } else {
            $results = null;
        }

        return response(['results' => $results]);
    }

    private function updateAggregatedResults(&$aggregatedResults, &$seasonResults, $prefix)
    {
        // Aggregate raw counts (e.g., ft_home_wins, ft_draws, etc.)
        foreach ($seasonResults as $key => $value) {
            if (str_starts_with($key, "{$prefix}_") && !str_contains($key, '_percentage')) {
                $aggregatedResults[$key] = ($aggregatedResults[$key] ?? 0) + $value;
            }
        }
    }

    private function initializeStatistics(): array
    {
        $keys = [
            'ft_counts',
            'ft_home_wins',
            'ft_draws',
            'ft_away_wins',
            'ft_gg',
            'ft_ng',
            'ft_over15',
            'ft_under15',
            'ft_over25',
            'ft_under25',
            'ft_over35',
            'ft_under35',
            'ht_counts',
            'ht_home_wins',
            'ht_draws',
            'ht_away_wins',
            'ht_gg',
            'ht_ng',
            'ht_over15',
            'ht_under15',
            'ht_over25',
            'ht_under25',
            'ht_over35',
            'ht_under35',
        ];
        return array_fill_keys($keys, 0);
    }

    public function store()
    {

        $competition_id = request()->competition_id;
        $season = Season::find(request()->season_id);
        $season_id = $season->id ?? 0;

        request()->merge([
            'per_page' => 700,
            'order_by' => 'utc_date',
            'order_direction' => 'asc',
            'without_response' => true,
            'is_competition_stats' => true,
        ]);

        $games = (new GameRepository(new Game()))->index(null, true);

        $games = $games['results']['data'];
        $ct = count($games);

        // echo "Total games for competition #{$competition_id}: $ct\n\n";

        $unique_dates_counts = count(array_unique(
            array_map(fn($c) => Carbon::parse($c)->format('Y-m-d'), array_column($games, 'utc_date'))
        ));
        // Log::info("Unique date counts: {$unique_dates_counts}");

        $statistics = $this->initializeStatistics();
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

            $statistics['ft_counts']++;

            $winningSide = GameComposer::winningSide($game, true);
            match ($winningSide) {
                0 => $statistics['ft_home_wins']++,
                1 => $statistics['ft_draws']++,
                2 => $statistics['ft_away_wins']++,
            };

            $bts = GameComposer::bts($game, true);
            if ($bts) {
                $statistics['ft_gg']++;
            } else {
                $statistics['ft_ng']++;
            }

            $goals = GameComposer::goals($game, true);
            if ($goals > 1) {
                $statistics['ft_over15']++;
            } else {
                $statistics['ft_under15']++;
            }
            if ($goals > 2) {
                $statistics['ft_over25']++;
            } else {
                $statistics['ft_under25']++;
            }
            if ($goals > 3) {
                $statistics['ft_over35']++;
            } else {
                $statistics['ft_under35']++;
            }

            $hasResults = GameComposer::hasResultsHT($game);
            if ($hasResults) {
                $statistics['ht_counts']++;

                $winningSide = GameComposer::winningSideHT($game, true);
                match ($winningSide) {
                    0 => $statistics['ht_home_wins']++,
                    1 => $statistics['ht_draws']++,
                    2 => $statistics['ht_away_wins']++,
                };

                $bts = GameComposer::btsHT($game, true);
                if ($bts) {
                    $statistics['ht_gg']++;
                } else {
                    $statistics['ht_ng']++;
                }

                $goals = GameComposer::goalsHT($game, true);
                if ($goals > 1) {
                    $statistics['ht_over15']++;
                } else {
                    $statistics['ht_under15']++;
                }
                if ($goals > 2) {
                    $statistics['ht_over25']++;
                } else {
                    $statistics['ht_under25']++;
                }
                if ($goals > 3) {
                    $statistics['ht_over35']++;
                } else {
                    $statistics['ht_under35']++;
                }
            }
        }

        if ($statistics['ft_counts'] > 0) {
            $data = [
                'competition_id' => $competition_id,
                'season_id' => $season_id,
                'ft_counts' => $statistics['ft_counts'],
                'ft_home_wins' => $statistics['ft_home_wins'],
                'ft_draws' => $statistics['ft_draws'],
                'ft_away_wins' => $statistics['ft_away_wins'],
                'ft_gg' => $statistics['ft_gg'],
                'ft_ng' => $statistics['ft_ng'],
                'ft_over15' => $statistics['ft_over15'],
                'ft_under15' => $statistics['ft_under15'],
                'ft_over25' => $statistics['ft_over25'],
                'ft_under25' => $statistics['ft_under25'],
                'ft_over35' => $statistics['ft_over35'],
                'ft_under35' => $statistics['ft_under35'],
                'ht_counts' => $statistics['ht_counts'],
                'ht_home_wins' => $statistics['ht_home_wins'],
                'ht_draws' => $statistics['ht_draws'],
                'ht_away_wins' => $statistics['ht_away_wins'],
                'ht_gg' => $statistics['ht_gg'],
                'ht_ng' => $statistics['ht_ng'],
                'ht_over15' => $statistics['ht_over15'],
                'ht_under15' => $statistics['ht_under15'],
                'ht_over25' => $statistics['ht_over25'],
                'ht_under25' => $statistics['ht_under25'],
                'ht_over35' => $statistics['ht_over35'],
                'ht_under35' => $statistics['ht_under35'],
            ];

            if ($season_id && (!request()->date || $unique_dates_counts > 1)) {

                CompetitionStatistic::updateOrCreate(
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                    ],
                    $data
                );
            } else {

                if (!$season_id) {
                    $game = Game::find($game['id']);
                    $season_id = $game->season_id ?? 0;
                }

                $data['date'] = Carbon::parse(request()->date)->format('Y-m-d');
                $data['matchday'] = $matchday;
                CompetitionStatistic::updateOrCreate(
                    [
                        'competition_id' => $competition_id,
                        'season_id' => $season_id,
                        'date' => $data['date'],
                        'matchday' => $data['matchday'],
                    ],
                    $data
                );
            }
        }

        Competition::find($competition_id)->update(['stats_last_done' => now()]);

        $arr = ['message' => 'Total matches ' . $ct . ', successfully done stats, (updated ' . $statistics['ft_counts'] . ').', 'results' => ['updated' => $statistics['ft_counts']]];
        if (request()->without_response) {
            return $arr;
        }
        return response($arr);
    }
}
