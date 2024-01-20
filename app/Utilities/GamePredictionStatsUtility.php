<?php

namespace App\Utilities;

use App\Repositories\GameComposer;

class GamePredictionStatsUtility
{
    const HOME_TEAM = 0;
    const DRAW = 1;
    const AWAY_TEAM = 2;

    const BATCH_SIZE = 500; // Adjust the batch size as needed

    protected $predictionTypeMode;

    function __construct()
    {
        $this->predictionTypeMode = request()->show_source_predictions ? 'source_prediction' : 'prediction';
    }

    public function doStats($games)
    {
        // Initialize stats array
        $stats = $this->initializeStats();
        $matchday = 0;
        $counts = 0;

        // Divide games into batches
        $gameBatches = array_chunk($games, self::BATCH_SIZE);

        foreach ($gameBatches as $batch) {
            $batchCounts = $this->processBatch($batch, $stats);
            $counts += $batchCounts;
        }

        $stats['counts'] = $counts;

        // Calculate and return overall stats
        return $this->calculateOverallStats($stats);
    }

    private function processBatch($batch, &$stats)
    {
        $batchCounts = 0;

        foreach ($batch as $game) {
            if (!$this->isValidGame($game)) {
                continue;
            }

            $this->updateMatchCounts($stats, $game);
            $this->updatePredictionStats($stats, $game);

            $this->updateBTSStats($stats, $game);
            $this->updateGoalStats($stats, $game);

            $winningSideHT = GameComposer::winningSideHT($game, true);
            $this->updateHalftimeCounts($stats, $winningSideHT);
            $this->updateHalftimePredictionStats($stats, $game, $winningSideHT);

            $batchCounts++;
        }

        return $batchCounts;
    }

    private function initializeStats()
    {
        $preset = ['counts' => 0, 'preds' => 0, 'preds_true' => 0, 'preds_true_percentage' => 0];
        $stats = [
            'full_time' => [
                'home_wins' => $preset,
                'draws' => $preset,
                'away_wins' => $preset,
                'gg' => $preset,
                'ng' => $preset,
                'over15' => $preset,
                'under15' => $preset,
                'over25' => $preset,
                'under25' => $preset,
                'over35' => $preset,
                'under35' => $preset,
            ],
            'half_time' => [
                'home_wins' => $preset,
                'draws' => $preset,
                'away_wins' => $preset,
            ],
        ];

        return $stats;
    }

    private function isValidGame($game)
    {
        $prediction = $game[$this->predictionTypeMode];
        return $prediction && isset($game['score']) && GameComposer::hasResults($game);
    }

    private function updateMatchCounts(&$stats, $game)
    {
        $winningSide = GameComposer::winningSide($game, true);

        if ($winningSide === self::HOME_TEAM) {
            $stats['full_time']['home_wins']['counts']++;
        } elseif ($winningSide === self::DRAW) {
            $stats['full_time']['draws']['counts']++;
        } elseif ($winningSide === self::AWAY_TEAM) {
            $stats['full_time']['away_wins']['counts']++;
        }
    }

    private function updatePredictionStats(&$stats, $game)
    {
        $prediction = $game[$this->predictionTypeMode];
        if (!$prediction) {
            return;
        }

        $winningSide = GameComposer::winningSide($game, true);

        $this->updatePredictionStat($stats['full_time']['home_wins'], $prediction['ft_hda_pick'], $winningSide, self::HOME_TEAM);
        $this->updatePredictionStat($stats['full_time']['draws'], $prediction['ft_hda_pick'], $winningSide, self::DRAW);
        $this->updatePredictionStat($stats['full_time']['away_wins'], $prediction['ft_hda_pick'], $winningSide, self::AWAY_TEAM);
    }

    private function updateHalftimeCounts(&$stats, $winningSideHT)
    {
        // Update halftime match counts
        if ($winningSideHT === self::HOME_TEAM) {
            $stats['half_time']['home_wins']['counts']++;
        } elseif ($winningSideHT === self::DRAW) {
            $stats['half_time']['draws']['counts']++;
        } elseif ($winningSideHT === self::AWAY_TEAM) {
            $stats['half_time']['away_wins']['counts']++;
        }
    }

    private function updateHalftimePredictionStats(&$stats, $game, $winningSideHT)
    {
        // Update halftime prediction stats
        $prediction = $game[$this->predictionTypeMode];
        if (!$prediction) {
            return;
        }

        $this->updatePredictionStat($stats['half_time']['home_wins'], $prediction['ht_hda_pick'], $winningSideHT, self::HOME_TEAM);
        $this->updatePredictionStat($stats['half_time']['draws'], $prediction['ht_hda_pick'], $winningSideHT, self::DRAW);
        $this->updatePredictionStat($stats['half_time']['away_wins'], $prediction['ht_hda_pick'], $winningSideHT, self::AWAY_TEAM);
    }

    private function updatePredictionStat(&$stat, $prediction, $actual, $outcome)
    {
        $stat['preds'] += ($prediction === $outcome) ? 1 : 0;
        $stat['preds_true'] += ($prediction === $outcome && $actual === $outcome) ? 1 : 0;
    }

    private function updateBTSStats(&$stats, $game)
    {
        $bts = GameComposer::bts($game, true);
        $this->updateBTSStat($stats['full_time']['gg'], $game[$this->predictionTypeMode]['bts_pick'], $bts, 1);
        $this->updateBTSStat($stats['full_time']['ng'], $game[$this->predictionTypeMode]['bts_pick'], $bts, 0);
    }

    private function updateBTSStat(&$stat, $prediction, $actual, $threshold)
    {
        if ($prediction > -1) {
            $stat['counts'] += $actual === $threshold ? 1 : 0;
            $stat['preds'] += ($prediction === $threshold) ? 1 : 0;
            $stat['preds_true'] += ($prediction === $threshold && $actual === $threshold) ? 1 : 0;
        }
    }

    private function updateGoalStats(&$stats, $game)
    {
        $goals = GameComposer::goals($game, true);

        if (!request()->get_prediction_stats) {
            $this->updateGoalStatOver($stats['full_time']['over15'], $game[$this->predictionTypeMode]['over_under15_pick'], $goals, 1);
            $this->updateGoalStatUnder($stats['full_time']['under15'], $game[$this->predictionTypeMode]['over_under15_pick'], $goals, 2);
        }

        $this->updateGoalStatOver($stats['full_time']['over25'], $game[$this->predictionTypeMode]['over_under25_pick'], $goals, 2, true);
        $this->updateGoalStatUnder($stats['full_time']['under25'], $game[$this->predictionTypeMode]['over_under25_pick'], $goals, 3, true);

        if (!request()->get_prediction_stats) {
            $this->updateGoalStatOver($stats['full_time']['over35'], $game[$this->predictionTypeMode]['over_under35_pick'], $goals, 3);
            $this->updateGoalStatUnder($stats['full_time']['under35'], $game[$this->predictionTypeMode]['over_under35_pick'], $goals, 4);
        }
    }

    private function updateGoalStatOver(&$stat, $prediction, $actual, $threshold, $constant_type = false)
    {
        if ($prediction > -1 && ($constant_type || !request()->get_prediction_stats)) {
            $stat['counts'] += ($actual > $threshold) ? 1 : 0;
            $stat['preds'] += ($prediction === 1) ? 1 : 0;
            $stat['preds_true'] += ($prediction === 1 && $actual > $threshold) ? 1 : 0;
        }
    }

    private function updateGoalStatUnder(&$stat, $prediction, $actual, $threshold, $constant_type = false)
    {
        if ($prediction > -1 && ($constant_type || !request()->get_prediction_stats)) {
            $stat['counts'] += ($actual < $threshold) ? 1 : 0;
            $stat['preds'] += ($prediction === 0) ? 1 : 0;
            $stat['preds_true'] += ($prediction === 0 && $actual < $threshold) ? 1 : 0;
        }
    }

    private function calculateOverallStats($stats)
    {
        // Calculate overall accuracy score
        $totalCorrectPredictions = 0;
        $totalPredictions = 0;

        foreach ($stats['full_time'] as $resultType => $resultStats) {
            $totalCorrectPredictions += $resultStats['preds_true'];
            $totalPredictions += $resultStats['preds'];
        }

        $averageScore = ($totalPredictions === 0) ? 0 : round($totalCorrectPredictions / $totalPredictions * 100);

        // Prepare and return stats array
        $arr = [
            'counts' => $stats['counts'],

            'full_time' => [
                'home_wins' => $this->calculatePercentageStats($stats['full_time']['home_wins']),
                'draws' => $this->calculatePercentageStats($stats['full_time']['draws']),
                'away_wins' => $this->calculatePercentageStats($stats['full_time']['away_wins']),
                'gg' => $this->calculatePercentageStats($stats['full_time']['gg']),
                'ng' => $this->calculatePercentageStats($stats['full_time']['ng']),
                'over15' => $this->calculatePercentageStats($stats['full_time']['over15']),
                'under15' => $this->calculatePercentageStats($stats['full_time']['under15']),
                'over25' => $this->calculatePercentageStats($stats['full_time']['over25']),
                'under25' => $this->calculatePercentageStats($stats['full_time']['under25']),
                'over35' => $this->calculatePercentageStats($stats['full_time']['over35']),
                'under35' => $this->calculatePercentageStats($stats['full_time']['under35']),
            ],
            'half_time' => [
                'home_wins' => $this->calculatePercentageStats($stats['half_time']['home_wins']),
                'draws' => $this->calculatePercentageStats($stats['half_time']['draws']),
                'away_wins' => $this->calculatePercentageStats($stats['half_time']['away_wins']),
            ],

            'average_score' => $averageScore,
        ];

        return $arr;
    }

    private function calculatePercentageStats($resultStats)
    {
        $percentage = ($resultStats['preds'] === 0) ? 0 : round($resultStats['preds_true'] / $resultStats['preds'] * 100);

        return [
            'counts' => $resultStats['counts'],
            'preds' => $resultStats['preds'],
            'preds_true' => $resultStats['preds_true'],
            'preds_true_percentage' => $percentage,
        ];
    }
}
