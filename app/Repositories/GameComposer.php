<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;

class GameComposer
{
    public static function team($team, $prefers = null)
    {
        if ($prefers === 'short') {
            return $team['short_name'];
        } elseif ($prefers === 'TLA') {
            return $team['tla'];
        } else {
            return $team['name'];
        }
    }

    public static function results($score, $type = 'ft', $show = null)
    {
        if ($type === 'ft') {
            $h = $score['home_scores_full_time'];
            $a = $score['away_scores_full_time'];
            if ($h !== null && $a !== null) {
                return ($show === 'h') ? $h : (($show === 'a') ? $a : "{$h} - {$a}");
            } else {
                return '-';
            }
        } elseif ($type === 'ht') {
            $h = $score['home_scores_half_time'];
            $a = $score['away_scores_half_time'];
            if ($h !== null && $a !== null) {
                return ($show === 'h') ? $h : (($show === 'a') ? $a : "{$h} - {$a}");
            } else {
                return '-';
            }
        } else {
            return $score['winner'] ?? 'U';
        }
    }

    public static function winner($game, $teamId)
    {
        $score = $game->score;
        if (!$score || !$score->winner) {
            return 'U';
        }

        if ($score->winner === 'DRAW') {
            return 'D';
        }

        if ($score->winner === 'HOME_TEAM') {
            return ($game->home_team_id === $teamId) ? 'W' : 'L';
        } elseif ($score->winner === 'AWAY_TEAM') {
            return ($game->away_team_id === $teamId) ? 'W' : 'L';
        }

        return 'U';
    }

    public static function winningSide($game, $integer = false)
    {
        $scoreData = $game->score;
        if (!$scoreData || !$scoreData->winner) {
            return $integer ? -1 : 'U';
        }


        if ($scoreData->winner === 'HOME_TEAM') {
            return $integer ? 0 : 'h';
        } elseif ($scoreData->winner === 'DRAW') {
            return $integer ? 1 : 'D';
        } elseif ($scoreData->winner === 'AWAY_TEAM') {
            return $integer ? 2 : 'a';
        }

        return $integer ? -1 : 'U';
    }

    public static function winningSideHT($game, $integer = false)
    {
        $scoreData = $game->score;
        if (!$scoreData || !$scoreData->winner) {
            return $integer ? -1 : 'U';
        }

        $homeTeamScore = (int)($scoreData->home_scores_half_time ?? 0);
        $awayTeamScore = (int)($scoreData->away_scores_half_time ?? 0);

        if ($homeTeamScore > $awayTeamScore) {
            $winner = 'HOME_TEAM';
        } elseif ($homeTeamScore < $awayTeamScore) {
            $winner = 'AWAY_TEAM';
        } else {
            $winner = 'DRAW';
        }

        if ($winner === 'HOME_TEAM') {
            return $integer ? 0 : 'h';
        } elseif ($winner === 'DRAW') {
            return $integer ? 1 : 'D';
        } elseif ($winner === 'AWAY_TEAM') {
            return $integer ? 2 : 'a';
        }

        return $integer ? -1 : 'U';
    }

    public static function goals($game)
    {
        $score = $game->score;
        if (!$score || !$score->winner) {
            return -1;
        }


        // Get the score data or provide default values if it's missing
        $scoreData = $game->score ?? [];
        $homeTeamScore = (int)($scoreData->home_scores_full_time ?? 0);
        $awayTeamScore = (int)($scoreData->away_scores_full_time ?? 0);

        return $homeTeamScore + $awayTeamScore;
    }

    public static function goalsHT($game)
    {
        $score = $game->score;
        if (!$score || !$score->winner) {
            return -1;
        }


        // Get the score data or provide default values if it's missing
        $scoreData = $game->score ?? [];
        $homeTeamScore = (int)($scoreData->home_scores_half_time ?? 0);
        $awayTeamScore = (int)($scoreData->away_scores_half_time ?? 0);

        return $homeTeamScore + $awayTeamScore;
    }

    public static function bts($game)
    {
        $score = $game->score;
        if (!$score || !$score->winner) {
            return -1;
        }

        // Get the score data or provide default values if it's missing
        $scoreData = $game->score ?? [];
        $homeTeamScore = (int)($scoreData->home_scores_full_time ?? 0);
        $awayTeamScore = (int)($scoreData->away_scores_full_time ?? 0);

        return ($homeTeamScore > 0 && $awayTeamScore > 0);
    }

    public static function btsHT($game)
    {
        $score = $game->score;
        if (!$score || !$score->winner) {
            return -1;
        }

        // Get the score data or provide default values if it's missing
        $scoreData = $game->score ?? [];
        $homeTeamScore = (int)($scoreData->home_scores_half_time ?? 0);
        $awayTeamScore = (int)($scoreData->away_scores_half_time ?? 0);

        return ($homeTeamScore > 0 && $awayTeamScore > 0);
    }

    public static function winnerId($game)
    {
        $score = $game->score;

        if (!$score || !$score->winner) {
            return null;
        }

        if ($score->winner === 'DRAW') {
            return null;
        }

        if ($score->winner === 'HOME_TEAM') {
            return $game->home_team_id;
        } elseif ($score->winner === 'AWAY_TEAM') {
            return $game->away_team_id;
        }

        return null;
    }

    public static function hasResults($game)
    {
        $score = $game->score;
        if (!$score || !$score->winner) {
            return null;
        }

        if ($score->winner === 'DRAW') {
            return true;
        }

        if ($score->winner === 'HOME_TEAM') {
            return true;
        } elseif ($score->winner === 'AWAY_TEAM') {
            return true;
        }

        return null;
    }

    public static function getScores($game, $teamId, $negate = false)
    {
        $homeTeamId = $game->home_team_id;
        $awayTeamId = $game->away_team_id;

        // Get the score data or provide default values if it's missing
        $scoreData = $game->score ?? [];
        $homeTeamScore = $scoreData->home_scores_full_time ?? 0;
        $awayTeamScore = $scoreData->away_scores_full_time ?? 0;

        // Calculate the scores for the specified team
        if ($homeTeamScore === $awayTeamScore) {
            $scores = $homeTeamScore;
        } else {
            if (!$negate) {
                if ($teamId === $homeTeamId) {
                    $scores = $homeTeamScore;
                } elseif ($teamId === $awayTeamId) {
                    $scores = $awayTeamScore;
                } else {
                    $scores = 0;
                }
            } else {
                if ($teamId === $homeTeamId) {
                    $scores = $awayTeamScore;
                } elseif ($teamId === $awayTeamId) {
                    $scores = $homeTeamScore;
                } else {
                    $scores = 0;
                }
            }
        }

        // Convert the scores to integers if they are strings
        $scores = (int)$scores;

        return $scores;
    }

    public static function getScoresHT($game, $teamId, $negate = false)
    {
        $homeTeamId = $game->home_team_id;
        $awayTeamId = $game->away_team_id;

        // Get the score data or provide default values if it's missing
        $scoreData = $game->score ?? [];
        $homeTeamScore = $scoreData->home_scores_half_time ?? 0;
        $awayTeamScore = $scoreData->away_scores_half_time ?? 0;

        // Calculate the scores for the specified team
        if ($homeTeamScore === $awayTeamScore) {
            $scores = $homeTeamScore;
        } else {
            if (!$negate) {
                if ($teamId === $homeTeamId) {
                    $scores = $homeTeamScore;
                } elseif ($teamId === $awayTeamId) {
                    $scores = $awayTeamScore;
                } else {
                    $scores = 0;
                }
            } else {
                if ($teamId === $homeTeamId) {
                    $scores = $awayTeamScore;
                } elseif ($teamId === $awayTeamId) {
                    $scores = $homeTeamScore;
                } else {
                    $scores = 0;
                }
            }
        }

        // Convert the scores to integers if they are strings
        $scores = (int)$scores;

        return $scores;
    }
}
