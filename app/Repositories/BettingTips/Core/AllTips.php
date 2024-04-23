<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;

class AllTips
{
    use BettingTipsTrait;

    /**
     * Fetch and process singles betting tips.
     *
     * @return array Processed singles betting tips with investment details.
     */
    function singles()
    {
        // Initialize arrays to store tips and their IDs
        $all_tips = [];
        $all_ids = [];

        // Fetch singles betting tips for various outcomes
        $this->fetchAllTips(new HomeWinTips(), $all_tips, $all_ids, true);
        $this->fetchAllTips(new DrawTips(), $all_tips, $all_ids, true);
        $this->fetchAllTips(new AwayWinTips(), $all_tips, $all_ids, true);
        $this->fetchAllTips(new Over25Tips(), $all_tips, $all_ids, true);
        $this->fetchAllTips(new Under25Tips(), $all_tips, $all_ids, true);
        $this->fetchAllTips(new GGTips(), $all_tips, $all_ids, true);
        $this->fetchAllTips(new NGTips(), $all_tips, $all_ids, true);

        // Get all games based on the fetched tips
        $results = $this->getAllGames($all_tips);

        // Calculate investment details for singles betting
        $investment = $this->singlesInvestment($results, null, null, $all_tips);

        // Paginate the results based on requested page size
        $results = $this->paginate($investment['betslips'], request()->per_page ?? 50);

        // Remove the 'betslips' key from investment data
        unset($investment['betslips']);

        // Add investment details to the results
        $results['investment'] = $investment;

        return $results;
    }

    /**
     * Fetch and process multiples betting tips.
     *
     * @return array Processed multiples betting tips with investment details.
     */
    function multiples()
    {
        // Initialize arrays to store tips and their IDs
        $all_tips = [];
        $all_ids = [];

        // Fetch multiples betting tips for various outcomes
        $this->fetchAllTips(new HomeWinTips(), $all_tips, $all_ids, false);
        $this->fetchAllTips(new DrawTips(), $all_tips, $all_ids, false);
        $this->fetchAllTips(new AwayWinTips(), $all_tips, $all_ids, false);
        $this->fetchAllTips(new Under25Tips(), $all_tips, $all_ids, false);
        $this->fetchAllTips(new GGTips(), $all_tips, $all_ids, false);
        $this->fetchAllTips(new NGTips(), $all_tips, $all_ids, false);

        // Get all games based on the fetched tips
        $results = $this->getAllGames($all_tips);

        // Calculate investment details for multiples betting
        $investment = $this->multiplesInvestment($results, null, null, $all_tips);

        // Paginate the results based on requested page size
        $results = $this->paginate($investment['betslips'], request()->per_page ?? 50);

        // Remove the 'betslips' key from investment data
        unset($investment['betslips']);

        // Add investment details to the results
        $results['investment'] = $investment;

        return $results;
    }
}
