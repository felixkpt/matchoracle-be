<?php
return [
    'investment' => [
        'initial_bankroll' => 1000,
        'singles_stake_ratio' => 0.1,
        'multiples_stake_ratio' => 0.1,
        'multiples_combined_min_odds' => 5,
    ],

    // Core tips
    'App\Repositories\BettingTips\Core\HomeWinTips' => [
        'outcome_name' => 'home_win',
        'odds_name' => 'home_win_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'ft_home_win_proba',
        'proba_threshold' => 50,
        'proba_name2' => 'ng_proba',
        'proba_threshold2' => 40
    ],
    'App\Repositories\BettingTips\Core\DrawTips' => [
        'outcome_name' => 'draw',
        'odds_name' => 'draw_odds',
        'odds_min_threshold' => 1.5,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'ft_draw_proba',
        'proba_threshold' => 43,
        'proba_name2' => 'under35_proba',
        'proba_threshold2' => 60
    ],
    'App\Repositories\BettingTips\Core\AwayWinTips' => [
        'outcome_name' => 'away_win',
        'odds_name' => 'away_win_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'ft_away_win_proba',
        'proba_threshold' => 50,
        'proba_name2' => 'over25_proba',
        'proba_threshold2' => 40
    ],
    'App\Repositories\BettingTips\Core\Over25Tips' => [
        'outcome_name' => 'over_25',
        'odds_name' => 'over_25_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'over25_proba',
        'proba_threshold' => 58,
        'proba_name2' => 'gg_proba',
        'proba_threshold2' => 55
    ],
    'App\Repositories\BettingTips\Core\Under25Tips' => [
        'outcome_name' => 'under_25',
        'odds_name' => 'under_25_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'under25_proba',
        'proba_threshold' => 60,
        'proba_name2' => 'ft_draw_proba',
        'proba_threshold2' => 34
    ],
    'App\Repositories\BettingTips\Core\GGTips' => [
        'outcome_name' => 'gg',
        'odds_name' => 'gg_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'gg_proba',
        'proba_threshold' => 56,
        'proba_name2' => 'ft_home_win_proba',
        'proba_threshold2' => 40
    ],
    'App\Repositories\BettingTips\Core\NGTips' => [
        'outcome_name' => 'ng',
        'odds_name' => 'ng_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'ng_proba',
        'proba_threshold' => 60,
        'proba_name2' => 'over25_proba',
        'proba_threshold2' => 46
    ],

    // Source tips
    'App\Repositories\BettingTips\Source\HomeWinTips' => [
        'outcome_name' => 'home_win',
        'odds_name' => 'home_win_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'ft_home_win_proba',
        'proba_threshold' => 50,
        'proba_name2' => 'ng_proba',
        'proba_threshold2' => 40
    ],
    'App\Repositories\BettingTips\Source\DrawTips' => [
        'outcome_name' => 'draw',
        'odds_name' => 'draw_odds',
        'odds_min_threshold' => 1.5,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'ft_draw_proba',
        'proba_threshold' => 43,
        'proba_name2' => 'under35_proba',
        'proba_threshold2' => 60
    ],
    'App\Repositories\BettingTips\Source\AwayWinTips' => [
        'outcome_name' => 'away_win',
        'odds_name' => 'away_win_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'ft_away_win_proba',
        'proba_threshold' => 50,
        'proba_name2' => 'over25_proba',
        'proba_threshold2' => 40
    ],
    'App\Repositories\BettingTips\Source\Over25Tips' => [
        'outcome_name' => 'over_25',
        'odds_name' => 'over_25_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'over25_proba',
        'proba_threshold' => 58,
        'proba_name2' => 'gg_proba',
        'proba_threshold2' => 55
    ],
    'App\Repositories\BettingTips\Source\Under25Tips' => [
        'outcome_name' => 'under_25',
        'odds_name' => 'under_25_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'under25_proba',
        'proba_threshold' => 60,
        'proba_name2' => 'ft_draw_proba',
        'proba_threshold2' => 34
    ],
    'App\Repositories\BettingTips\Source\GGTips' => [
        'outcome_name' => 'gg',
        'odds_name' => 'gg_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'gg_proba',
        'proba_threshold' => 56,
        'proba_name2' => 'ft_home_win_proba',
        'proba_threshold2' => 40
    ],
    'App\Repositories\BettingTips\Source\NGTips' => [
        'outcome_name' => 'ng',
        'odds_name' => 'ng_odds',
        'odds_min_threshold' => 1.3,
        'odds_max_threshold' => 6.0,
        'proba_name' => 'ng_proba',
        'proba_threshold' => 60,
        'proba_name2' => 'over25_proba',
        'proba_threshold2' => 46
    ]
];
