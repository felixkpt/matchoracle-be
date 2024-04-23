<?php

namespace App\Repositories\BettingTips;

use App\Models\Game;
use App\Repositories\GameComposer;
use App\Utilities\GameUtility;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

trait BettingTipsTrait
{
    private $initial_bankroll = 10000;
    private $singles_stake_ratio = 0.1;
    private $multiples_stake_ratio = 0.1;
    private $multiples_combined_min_odds = 5;

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
                if (property_exists($this, $propertyName)) {
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

    private function singlesInvestment($results, $odds_name = null, $outcome_name = null, $all_tips = null)
    {
        $initial_bankroll = $this->initial_bankroll;
        $bankroll_deposits = 1;
        $current_bankroll = $initial_bankroll;
        $stake = $this->singles_stake_ratio * $initial_bankroll;
        $min_odds = 1;

        $results = $results->orderBy('utc_date', 'asc')->get(-1)['data'];

        $total = $won = $gain = 0;
        $longest_winning_streak = $longest_losing_streak = 0;
        $current_winning_streak = $current_losing_streak = 0;
        $total_odds = 0; // Variable to store total odds for multiples

        $odds = 1.1;
        $outcome = 'W';
        $betslips = [];
        $betslip = [];
        $final_bankroll = $current_bankroll;
        foreach ($results as $game) {

            if (is_array($all_tips) && isset($game['id'])) {
                // If $type is an array and $game->id exists, search for the game ID in $type
                if (in_array($game['id'], array_column($all_tips, 'id'))) {
                    $tipIndex = array_search($game['id'], array_column($all_tips, 'id'));
                    $odds_name = $all_tips[$tipIndex]['odds_name'];
                    $outcome_name = $all_tips[$tipIndex]['outcome_name'];
                }
            }

            if ($game['Winner'] == 'POSTPONED') continue;

            $odds = $game['odds'][0]->{$odds_name};


            if ($game['outcome'] == 'L') $outcome = 'L';
            else if ($game['outcome'] == 'U' && $outcome != 'L') $outcome = 'U';

            $betslip[] = [
                'game' => $game,
                'odds_name' => $odds_name,
                'odds_name_print' => $this->formatOutcomeName($outcome_name),
            ];

            if ($odds >= $min_odds) {
                $total++;

                // Check if the current bankroll is sufficient for the stake
                if ($current_bankroll - $stake < 0) {
                    // If not, apply a top-up
                    $current_bankroll = $initial_bankroll;
                    $bankroll_deposits++;
                }

                if ($outcome == 'W') {
                    // Accumulate total odds
                    $total_odds += $odds;

                    $gain += ($stake * $odds) - $stake;
                    $won++;

                    // Update streaks
                    $current_winning_streak++;
                    $current_losing_streak = 0;

                    // Update longest winning streak
                    if ($current_winning_streak > $longest_winning_streak) {
                        $longest_winning_streak = $current_winning_streak;
                    }
                } elseif ($outcome == 'L') {

                    $gain -= $stake;

                    // Update streaks
                    $current_losing_streak++;
                    $current_winning_streak = 0;

                    // Update longest losing streak
                    if ($current_losing_streak > $longest_losing_streak) {
                        $longest_losing_streak = $current_losing_streak;
                    }
                }

                $current_bankroll += $gain;
                $final_bankroll = $current_bankroll;
                $final_bankroll_formatted = number_format($final_bankroll, 2, '.', request()->without_response ? '' : ',');
                $betslips[] = [
                    'betslip' => $betslip,
                    'odds' => number_format($odds, 2, '.', ''),
                    'stake' => $stake,
                    'outcome' => $outcome,
                    'bankroll_deposits' => $bankroll_deposits,
                    'final_bankroll' => $final_bankroll_formatted,
                    'odds_name' => $odds_name,
                    'odds_name_print' => $this->formatOutcomeName($outcome_name),
                ];
                // reset
                $odds = 1;
                $outcome = 'W';
                $betslip = [];
                $gain = 0;
            }
        }

        // Calculate average odds for multiples
        $average_won_odds = $won > 0 ? number_format($total_odds / $won, 2, '.', '') : 0;

        // Handle the case where all outcomes are wins or losses
        if ($current_winning_streak === count($results) && $current_winning_streak > $longest_winning_streak) {
            $longest_winning_streak = $current_winning_streak;
        } elseif ($current_losing_streak === count($results) && $current_losing_streak > $longest_losing_streak) {
            $longest_losing_streak = $current_losing_streak;
        }

        $gain = $final_bankroll - $initial_bankroll;
        $gain = number_format($gain, 2, '.', request()->without_response ? '' : ',');
        $roi = number_format(($final_bankroll / $initial_bankroll) * 100, 0, '.', request()->without_response ? '' : ',');

        $initial_bankroll_formatted = number_format($initial_bankroll, 2, '.', request()->without_response ? '' : ',');
        $final_bankroll_formatted = number_format($final_bankroll, 2, '.', request()->without_response ? '' : ',');

        return [
            'betslips' => array_reverse($betslips),
            'initial_bankroll' => $initial_bankroll_formatted,
            'bankroll_deposits' => $bankroll_deposits,
            'final_bankroll' => $final_bankroll_formatted,
            'total' => $total,
            'won' => $won,
            'won_percentage' => $won > 0 ? intval($won / $total * 100) : 0,
            'average_won_odds' => $average_won_odds,
            'gain' => $gain,
            'roi' => $roi,
            'longest_winning_streak' => $longest_winning_streak,
            'longest_losing_streak' => $longest_losing_streak,
        ];
    }

    private function multiplesInvestment($results, $odds_name = null, $outcome_name = null, $all_tips = null)
    {
        $initial_bankroll = $this->initial_bankroll;
        $bankroll_deposits = 1;
        $current_bankroll = $initial_bankroll;
        $stake = $this->multiples_stake_ratio * $initial_bankroll;
        $min_odds = $this->multiples_combined_min_odds;

        $results = $results->orderBy('utc_date', 'asc')->get(-1)['data'];

        $total = $won = $gain = 0;
        $longest_winning_streak = $longest_losing_streak = 0;
        $current_winning_streak = $current_losing_streak = 0;
        $total_odds = 0; // Variable to store total odds for multiples

        $odds = 1;
        $outcome = 'W';
        $betslips = [];
        $betslip = [];
        $final_bankroll = $current_bankroll;
        foreach ($results as $game) {

            if (is_array($all_tips) && isset($game['id'])) {
                // If $type is an array and $game->id exists, search for the game ID in $type
                if (in_array($game['id'], array_column($all_tips, 'id'))) {
                    $tipIndex = array_search($game['id'], array_column($all_tips, 'id'));
                    $odds_name = $all_tips[$tipIndex]['odds_name'];
                    $outcome_name = $all_tips[$tipIndex]['outcome_name'];
                }
            }

            if ($game['Winner'] == 'POSTPONED') continue;

            $odds *= $game['odds'][0]->{$odds_name};

            if ($game['outcome'] == 'L') $outcome = 'L';
            else if ($game['outcome'] == 'U' && $outcome != 'L') $outcome = 'U';

            $betslip[] = [
                'game' => $game,
                'odds_name' => $odds_name,
                'odds_name_print' => $this->formatOutcomeName($outcome_name),
            ];

            if ($odds >= $min_odds) {
                $total++;

                // Check if the current bankroll is sufficient for the stake
                if ($current_bankroll - $stake < 0) {
                    // If not, apply a top-up
                    $current_bankroll = $initial_bankroll;
                    $bankroll_deposits++;
                }

                if ($outcome == 'W') {
                    // Accumulate total odds
                    $total_odds += $odds;

                    $gain += ($stake * $odds) - $stake;
                    $won++;

                    // Update streaks
                    $current_winning_streak++;
                    $current_losing_streak = 0;

                    // Update longest winning streak
                    if ($current_winning_streak > $longest_winning_streak) {
                        $longest_winning_streak = $current_winning_streak;
                    }
                } elseif ($outcome == 'L') {
                    $gain -= $stake;

                    // Update streaks
                    $current_losing_streak++;
                    $current_winning_streak = 0;

                    // Update longest losing streak
                    if ($current_losing_streak > $longest_losing_streak) {
                        $longest_losing_streak = $current_losing_streak;
                    }
                }

                $current_bankroll += $gain;
                $final_bankroll = $current_bankroll;
                $final_bankroll_formatted = number_format($final_bankroll, 2, '.', request()->without_response ? '' : ',');
                $betslips[] = [
                    'betslip' => $betslip,
                    'odds' => number_format($odds, 2, '.', ''),
                    'stake' => $stake,
                    'bankroll_deposits' => $bankroll_deposits,
                    'final_bankroll' => $final_bankroll_formatted,
                    'outcome' => $outcome,
                ];

                // reset
                $odds = 1;
                $outcome = 'W';
                $betslip = [];
                $gain = 0;
            }
        }

        // Calculate average odds for multiples
        $average_won_odds = $won > 0 ? number_format($total_odds / $won, 2, '.', '') : 0;

        // Handle the case where all outcomes are wins or losses
        if ($current_winning_streak === count($results) && $current_winning_streak > $longest_winning_streak) {
            $longest_winning_streak = $current_winning_streak;
        } elseif ($current_losing_streak === count($results) && $current_losing_streak > $longest_losing_streak) {
            $longest_losing_streak = $current_losing_streak;
        }

        $gain = $final_bankroll - $initial_bankroll;
        $gain = number_format($gain, 2, '.', request()->without_response ? '' : ',');
        $roi = number_format(($final_bankroll / $initial_bankroll) * 100, 0, '.', request()->without_response ? '' : ',');

        $initial_bankroll_formatted = number_format($initial_bankroll, 2, '.', request()->without_response ? '' : ',');
        $final_bankroll_formatted = number_format($final_bankroll, 2, '.', request()->without_response ? '' : ',');

        return [
            'betslips' => array_reverse($betslips),
            'initial_bankroll' => $initial_bankroll_formatted,
            'bankroll_deposits' => $bankroll_deposits,
            'final_bankroll' => $final_bankroll_formatted,
            'total' => $total,
            'won' => $won,
            'won_percentage' => $won > 0 ? intval($won / $total * 100) : 0,
            'average_won_odds' => $average_won_odds,
            'gain' => $gain,
            'roi' => $roi,
            'longest_winning_streak' => $longest_winning_streak,
            'longest_losing_streak' => $longest_losing_streak,
        ];
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


    private function calculateStreaks($outcomes)
    {
        $longest_winning_streak = $longest_losing_streak = 0;
        $current_winning_streak = $current_losing_streak = 0;

        foreach ($outcomes as $outcome) {
            if ($outcome == 'W') {
                // Update streaks
                $current_winning_streak++;
                $current_losing_streak = 0;

                // Update longest winning streak
                if ($current_winning_streak > $longest_winning_streak) {
                    $longest_winning_streak = $current_winning_streak;
                }
            } elseif ($outcome == 'L') {
                // Update streaks
                $current_losing_streak++;
                $current_winning_streak = 0;

                // Update longest losing streak
                if ($current_losing_streak > $longest_losing_streak) {
                    $longest_losing_streak = $current_losing_streak;
                }
            }
        }

        return [
            'longest_winning_streak' => $longest_winning_streak,
            'longest_losing_streak' => $longest_losing_streak,
        ];
    }


    private function lastPredsStatePassed($game)
    {
        $home_team_prev_games = Game::with(['score'])->where('home_team_id', $game->home_team_id)->where('utc_date', '<', Carbon::today())->whereHas('prediction')->whereHas('score', fn ($q) => $q->whereNotNull('home_scores_full_time'))->take(3)->orderBy('utc_date', 'desc')->get();
        $away_team_prev_games = Game::with(['score'])->where('away_team_id', $game->away_team_id)->where('utc_date', '<', Carbon::today())->whereHas('prediction')->whereHas('score', fn ($q) => $q->whereNotNull('home_scores_full_time'))->take(3)->orderBy('utc_date', 'desc')->get();

        $home_team_correct_preds = 0;
        foreach ($home_team_prev_games as $game) {
            $pred = $game->prediction;
            $hda = GameComposer::winningSide($game, true);
            if ($pred->hda == $hda) $home_team_correct_preds++;
        }

        $away_team_correct_preds = 0;
        foreach ($away_team_prev_games as $game) {
            $pred = $game->prediction;
            $hda = GameComposer::winningSide($game, true);
            if ($pred->hda == $hda) $away_team_correct_preds++;
        }

        return $home_team_correct_preds > 0 && $away_team_correct_preds > 0;
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

    function formatOutcomeName($outcome_name)
    {
        $name = $outcome_name;
        if ($name == 'gg') return 'GG';
        if ($name == 'ng') return 'NG';

        return Str::ucfirst(Str::replace('_', ' ', $name));
    }
}
