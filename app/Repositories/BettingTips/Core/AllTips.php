<?php

namespace App\Repositories\BettingTips\Core;

use App\Repositories\BettingTips\BettingTipsTrait;
use App\Utilities\GameUtility;

class AllTips
{
    use BettingTipsTrait;

    function singles()
    {
        $allTips = [];
        $allIds = [];

        // Get home, draw, and away game IDs separately
        $modelTips = (new HomeWinTips());
        $modelIds = $modelTips->singles(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new DrawTips());
        $modelIds = $modelTips->singles(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new AwayWinTips());
        $modelIds = $modelTips->singles(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new Over25Tips());
        $modelIds = $modelTips->singles(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new Under25Tips());
        $modelIds = $modelTips->singles(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new GGTips());
        $modelIds = $modelTips->singles(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new NGTips());
        $modelIds = $modelTips->singles(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);


        // Retrieve games using the merged IDs
        $results = $this->getGames($allTips);

        $investment = $this->singlesInvestment($results, null, null, $allTips);

        $results = $investment['betslips'];
        $results = $this->paginate($results, request()->per_page ?? 50);

        unset($investment['betslips']);
        $results['investment'] = $investment;

        return $results;
    }

    function multiples()
    {
        $allTips = [];
        $allIds = [];

        // Get home, draw, and away game IDs separately
        $modelTips = (new HomeWinTips());
        $modelIds = $modelTips->multiples(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new DrawTips());
        $modelIds = $modelTips->multiples(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new AwayWinTips());
        $modelIds = $modelTips->multiples(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new Under25Tips());
        $modelIds = $modelTips->multiples(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new GGTips());
        $modelIds = $modelTips->multiples(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        $modelTips = (new NGTips());
        $modelIds = $modelTips->multiples(true);
        $odds_name = $modelTips->odds_name;
        $outcome_name = $modelTips->outcome_name;
        // allTips mapper
        $allTips = array_merge($allTips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $modelIds));
        $allIds = array_merge($allIds, $modelIds);
        // Merge IDs into request for filtering
        request()->merge(['exclude_ids' => $allIds]);

        // Retrieve games using the merged IDs
        $results = $this->getGames($allTips);

        $investment = $this->multiplesInvestment($results, null, null, $allTips);

        $results = $investment['betslips'];
        $results = $this->paginate($results, request()->per_page ?? 50);

        unset($investment['betslips']);
        $results['investment'] = $investment;

        return $results;
    }

    function getGames($allTips)
    {
        $include_ids = array_column($allTips, 'id');

        if (count($include_ids) === 0) {
            $include_ids = [-1];
        }

        request()->merge(['exclude_ids' => null, 'include_ids' => $include_ids]);

        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters();

        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, $allTips));

        return $results;
    }
}
