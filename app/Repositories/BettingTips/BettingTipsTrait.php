<?php

namespace App\Repositories\BettingTips;

use App\Repositories\GameComposer;
use App\Utilities\GameUtility;
use Illuminate\Pagination\LengthAwarePaginator;

trait BettingTipsTrait
{
    protected $initial_bankroll = 10000;
    protected $singles_stake_ratio = 0.1;
    protected $multiples_stake_ratio = 0.1;
    protected $multiples_combined_min_odds = 5;

    use CalculateInvestment;

    // Define the outcome name
    public $outcome_name = 'outcome_name_placeholder';

    // Define the odds name
    public $odds_name = 'odds_name_placeholder';

    // Define thresholds for minimum and maximum odds
    private $odds_min_threshold = 1.3;
    private $odds_max_threshold = 6.0;

    // Define the name and threshold for the first probability used in prediction
    private $proba_name = 'proba_name_placeholder';
    private $proba_threshold = 58;

    // Define the name and threshold for the second probability used in prediction
    private $proba_name2 = 'proba_name2_placeholder';
    private $proba_threshold2 = 55;

    protected function setTipsProperties($tipsClassName)
    {
        $config = config("betting_tips.$tipsClassName");

        if ($config) {
            foreach ($config as $propertyName => $propertyValue) {
                // Check if the property exists and is not null
                if (property_exists($this, $propertyName)) {
                    $this->$propertyName = $propertyValue;
                }
            }
        }

        $config = config("betting_tips.investment");

        if ($config) {
            foreach ($config as $propertyName => $propertyValue) {
                // Check if the property exists and is not null
                if (property_exists($this, $propertyName) && $propertyValue !== null) {
                    $this->$propertyName = $propertyValue;
                }
            }
        }
    }

    function singles($isAllTips = false)
    {
        $results = $this->getGames($this->outcome_name, $isAllTips);

        if ($isAllTips) {
            return $results->pluck('id')->toArray();
        }

        $investment = $this->singlesInvestment($results, $this->odds_name, $this->outcome_name);

        $results = $investment['betslips'];
        $results = $this->paginate($results, request()->per_page ?? 50);

        unset($investment['betslips']);
        $results['investment'] = $investment;

        return $results;
    }

    function multiples($isAllTips = false)
    {
        $results = $this->getGames($this->outcome_name, $isAllTips);

        if ($isAllTips) {
            return $results->pluck('id')->toArray();
        }

        $investment = $this->multiplesInvestment($results, $this->odds_name, $this->outcome_name);

        $results = $investment['betslips'];
        $results = $this->paginate($results, request()->per_page ?? 50);

        unset($investment['betslips']);
        $results['investment'] = $investment;

        return $results;
    }

    function getGames($outcome_name, $isAllTips = false)
    {

        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters()
            ->whereHas('odds', fn ($q) => $this->oddsRange($q));

        if (request()->prediction_mode_id == 1 || !request()->prediction_mode_id) {
            $results = $results->whereHas('prediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold)->where($this->proba_name2, '>=', $this->proba_threshold2))
                ->whereHas('competition.predictionStatistic', fn ($q) => $this->predictionStatisticFilter($q));
        } else if (request()->prediction_mode_id == 2) {
            $results = $results->whereHas('sourcePrediction', fn ($q) => $q->where($this->proba_name, '>=', $this->proba_threshold)->where($this->proba_name2, '>=', $this->proba_threshold2));
        }

        if (!$isAllTips) {
            $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, $outcome_name));
        }

        return $results;
    }

    function getAllGames($all_tips)
    {
        $include_ids = array_column($all_tips, 'id');
        if (count($include_ids) === 0) {
            $include_ids = [-1];
        }

        request()->merge(['exclude_ids' => null, 'include_ids' => $include_ids]);

        $gameUtilities = new GameUtility();
        $results = $gameUtilities->applyGameFilters();
        $results = $gameUtilities->formatGames($results)->addColumn('outcome', fn ($q) => $this->getOutcome($q, $all_tips));

        return $results;
    }

    function oddsRange($q)
    {
        $q->where($this->odds_name, '>=', $this->odds_min_threshold)->where($this->odds_name, '<=', $this->odds_max_threshold);
    }

    /**
     * Get the outcome of a game based on the provided type(s).
     *
     * @param object $game The game object.
     * @param mixed $type The type of outcome or array of outcomes to consider.
     *                    Can be a string or an array of strings representing the outcome type(s).
     *                    If it's an array, it's assumed to contain associative arrays with 'id' and 'outcome_name' keys.
     * @return string The outcome of the game ('W' for win, 'L' for lose, 'U' for undecided, 'PST' for postponed).
     */
    private function getOutcome($game, $type)
    {
        if (is_array($type) && isset($game->id)) {
            // If $type is an array and $game->id exists, search for the game ID in $type
            if (in_array($game->id, array_column($type, 'id'))) {
                $tipIndex = array_search($game->id, array_column($type, 'id'));
                $type = $type[$tipIndex]['outcome_name'];
            }
        }

        if ($game->score?->winner == 'POSTPONED') return 'PST';
        if (!GameComposer::hasResults($game)) return 'U';

        switch ($type) {
            case 'home_win':
                return GameComposer::winningSide($game, true) == 0 ? 'W' : 'L';
            case 'draw':
                return GameComposer::winningSide($game, true) == 1 ? 'W' : 'L';
            case 'away_win':
                return GameComposer::winningSide($game, true) == 2 ? 'W' : 'L';
            case 'gg':
                return GameComposer::bts($game, true) ? 'W' : 'L';
            case 'ng':
                return !GameComposer::bts($game, true) ? 'W' : 'L';
            case 'over_25':
                return GameComposer::goals($game, true) > 2 ? 'W' : 'L';
            case 'under_25':
                return GameComposer::goals($game, true) <= 2 ? 'W' : 'L';
            default:
                return 'U'; // Unknown outcome
        }
    }

    function fetchAllTips($typeTips, &$all_tips, &$all_ids, $isSingles)
    {
        $typeIds = $isSingles ? $typeTips->singles(true) : $typeTips->multiples(true);
        $odds_name = $typeTips->odds_name;
        $outcome_name = $typeTips->outcome_name;

        $all_tips = array_merge($all_tips, array_map(fn ($id) => ['id' => $id, 'odds_name' => $odds_name, 'outcome_name' => $outcome_name], $typeIds));
        $all_ids = array_merge($all_ids, $typeIds);
        request()->merge(['exclude_ids' => $all_ids]);
    }

    private function paginate($results, $perPage)
    {
        $perPage = $perPage ?? request()->per_page ?? 50;
        $page = request()->page ?? 1;

        $offset = ($page - 1) * $perPage;

        $items = array_slice($results, $offset, $perPage);

        $paginator = new LengthAwarePaginator(
            $items,
            count($results),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return [
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
