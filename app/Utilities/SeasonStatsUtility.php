<?php

namespace App\Utilities;

use App\Models\Season;
use App\Models\GameLastAction;

class SeasonStatsUtility
{
    public function updateSeasonStats(Season $season): Season
    {
        $counts = $season->games()
        ->join('game_last_actions as gla', 'games.id', '=', 'gla.game_id')
        ->selectRaw('
                COUNT(*) as games_total_count,
        
                SUM(CASE WHEN gla.ft_status = ? THEN 1 ELSE 0 END) as ft_pending_count,
                SUM(CASE WHEN gla.ft_status = ? THEN 1 ELSE 0 END) as ft_fetched_count,
                SUM(CASE WHEN gla.ft_status = ? THEN 1 ELSE 0 END) as ft_missing_count,
        
                SUM(CASE WHEN gla.ht_status = ? THEN 1 ELSE 0 END) as ht_pending_count,
                SUM(CASE WHEN gla.ht_status = ? THEN 1 ELSE 0 END) as ht_fetched_count,
                SUM(CASE WHEN gla.ht_status = ? THEN 1 ELSE 0 END) as ht_missing_count,
        
                SUM(CASE WHEN gla.odd_ft_status = ? THEN 1 ELSE 0 END) as odd_ft_pending_count,
                SUM(CASE WHEN gla.odd_ft_status = ? THEN 1 ELSE 0 END) as odd_ft_fetched_count,
                SUM(CASE WHEN gla.odd_ft_status = ? THEN 1 ELSE 0 END) as odd_ft_missing_count,
        
                SUM(CASE WHEN gla.odd_ht_status = ? THEN 1 ELSE 0 END) as odd_ht_pending_count,
                SUM(CASE WHEN gla.odd_ht_status = ? THEN 1 ELSE 0 END) as odd_ht_fetched_count,
                SUM(CASE WHEN gla.odd_ht_status = ? THEN 1 ELSE 0 END) as odd_ht_missing_count
            ', [
        GameLastAction::STATUS_PENDING, GameLastAction::STATUS_FETCHED, GameLastAction::STATUS_MISSING,
        GameLastAction::STATUS_PENDING, GameLastAction::STATUS_FETCHED, GameLastAction::STATUS_MISSING,
        GameLastAction::STATUS_PENDING, GameLastAction::STATUS_FETCHED, GameLastAction::STATUS_MISSING,
        GameLastAction::STATUS_PENDING, GameLastAction::STATUS_FETCHED, GameLastAction::STATUS_MISSING,
        ])
        ->first()
        ->toArray();
        
        // Update season in DB
        $season->update($counts);
        
        return $season->refresh();
    }
}
