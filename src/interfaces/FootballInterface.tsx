export interface CountryInterface {
    id: string;
    name: string;
    slug: string;
    code?: string | null;
    dial_code?: string | null;
    flag?: string | null;
    logo?: string | null;
    continent_id: string;
    has_competitions: boolean;
    competitions: CompetitionInterface[];
    priority_number: number;
    status_id: string;
    user_id: string;
    created_at: string;
    updated_at: string;
}

export interface TeamInterface {
    id: string;
    name: string;
    slug: string;
    short_name: string;
    tla: string;
    logo: string;
    address_id: string | null;
    website: string | null;
    founded: string | null;
    club_colors: string | null;
    venue_id: string | null;
    coach_id: string | null;
    coach_contract: any;
    competition_id: string;
    competition: CompetitionInterface;
    competitions: CompetitionInterface[];
    continent_id: string;
    country_id: string | null;
    country: CountryInterface;
    priority_number: number;
    last_updated: string | null;
    last_fetch: string | null;
    last_detailed_fetch: string | null;
    status_id: string;
    user_id: string;
    created_at: string;
    updated_at: string;
}

export interface StandingTableInterface {
    id: string;
    season_id: string;
    standing_id: string;
    team_id: string;
    position: number;
    played_games: number;
    form: string | null;
    won: number;
    draw: number;
    lost: number;
    points: number;
    goals_for: number;
    goals_against: number;
    goal_difference: number;
    status_id: string;
    user_id: string;
    created_at: string;
    updated_at: string;
    team: TeamInterface;
}

export interface StandingInterface {
    id: string;
    competition_id: string;
    season_id: string;
    season: SeasonInterface;
    stage: string;
    type: string;
    group: string | null;
    status_id: string;
    user_id: string;
    created_at: string;
    updated_at: string;
    standing_table: StandingTableInterface[];
}

export interface SeasonInterface {
    id: string;
    competition_id: string;
    start_date: string;
    end_date: string;
    is_current: number;
    current_matchday: number | null;
    total_matchdays: number | null;
    winner_id: string | null;
    stages: any[]; // Define the structure based on your data
    status_id: string;
    user_id: string;
    created_at: string;
    updated_at: string;
}

export interface CompetitionInterface {
    id: string;
    name: string;
    slug: string;
    code: string;
    type: string;
    logo: string | null;
    plan: string | null;
    abbreviation: string | null;
    has_teams: boolean | null;
    continent_id: string;
    country_id: string | null;
    country: CountryInterface;
    priority_number: number;
    predictions_last_train: string | null
    Predictions_last_train: string | null
    predictions_trained_to: string | null
    last_updated: string;
    last_fetch: string | null;
    last_detailed_fetch: string | null;
    image: string | null;
    stage_id: string;
    status_id: string;
    user_id: string;
    created_at: string;
    updated_at: string;
    standings: StandingInterface[];
    seasons: SeasonInterface[];
    games_per_season: number;
    available_seasons: number;
}
export interface ScoreInterface {
    id: string;
    game_id: string;
    winner: string;
    duration: string;
    home_scores_full_time: string;
    away_scores_full_time: string;
    home_scores_half_time: string;
    away_scores_half_time: string;
    created_at: string;
    updated_at: string;
}

export interface PredictionInterface {
    type: string;
    game_id: string;
    ft_hda: string;
    ft_home_win_proba: number;
    ft_draw_proba: number;
    ft_away_win_proba: number;
    ht_hda: string;
    ht_home_win_proba: number;
    ht_draw_proba: number;
    ht_away_win_proba: number;
    gg: string;
    gg_proba: number;
    ng_proba: number;
    over25: string;
    over25_proba: number;
    under25_proba: number;
    cs: string;
    cs_proba: number;
}

export interface CurrentUserVotes {
    winner: string
    over_under: string
    bts: string
}

export interface OddInterface {
    id: string
    home_win_odds: string
    draw_odds: string
    away_win_odds: string
    over_25_odds: string
    under_25_odds: string
    gg_odds: string
    ng_odds: string
}
export interface GameInterface {
    id: string;
    competition_id: string;
    home_team_id: string;
    away_team_id: string;
    season_id: string;
    country_id: string;
    is_future: boolean;
    utc_date: string;
    status: string;
    matchday: number;
    stage: string;
    group: string | null;
    last_updated: string;
    last_fetch: string;
    priority_number: number;
    status_id: string;
    user_id: string;
    created_at: string;
    updated_at: string;
    full_time: string;
    half_time: string;
    Created_at: string;
    Status: string;
    action: string;
    competition: CompetitionInterface;
    home_team: TeamInterface;
    away_team: TeamInterface;
    score: ScoreInterface;
    home_win_votes: number
    draw_votes: number
    away_win_votes: number
    over_votes: number
    under_votes: number
    gg_votes: number
    ng_votes: number
    current_user_votes: CurrentUserVotes,
    user_winner_vote: string | boolean
    user_over_under_vote: string | boolean
    user_bts_vote: string | boolean

    prediction: PredictionInterface,
    formatted_prediction: PredictionInterface,
    CS: string;
    odds: OddInterface[]
    Fulltime: HTMLElement
    outcome: 'W' | 'L' | 'U'
    Winner: string

}

export interface PredictionCategoryInterface {
    counts: number;
    preds: number;
    preds_true: number;
    preds_true_percentage: number;
}

export interface PredictionStatisticsInterface {
    counts: number;
    ft: {
        home_wins: PredictionCategoryInterface;
        draws: PredictionCategoryInterface;
        away_wins: PredictionCategoryInterface;
        gg: PredictionCategoryInterface;
        ng: PredictionCategoryInterface;
        over15: PredictionCategoryInterface;
        under15: PredictionCategoryInterface;
        over25: PredictionCategoryInterface;
        under25: PredictionCategoryInterface;
        over35: PredictionCategoryInterface;
        under35: PredictionCategoryInterface;
    };
    ht: {
        home_wins: PredictionCategoryInterface;
        draws: PredictionCategoryInterface;
        away_wins: PredictionCategoryInterface;
    };
    average_score: number;
}

export interface GameSourceInterface {
    id: string
    name: string
    url: string
    description: string
    priority_number: number
}
export interface CompetitionGameSourceInterface {
    id: string
    competition_id: string
    game_source_id: string
    source_uri: string
    source_id: string
    subscription_expires: string
    is_subscribed: string
    priority_number: number
    pivot: any
}

export interface CompetitionTabInterface {
    record: CompetitionInterface | undefined;
    seasons: SeasonInterface[] | null
    selectedSeason: SeasonInterface | null
    setSelectedSeason: React.Dispatch<React.SetStateAction<SeasonInterface | null>>;
    mainKey: any
    setMainKey?: React.Dispatch<React.SetStateAction<number>>;
    useDate?: boolean;
    isDisabled?: boolean
    setUseDates: any
}

export interface SeasonsListInterface {
    seasons: SeasonInterface[] | null
    selectedSeason: SeasonInterface | null
    handleSeasonChange?: (season: SeasonInterface | null) => void;

}
export interface DashJobLogsInterface {
    total_job_run_counts: number;
    total_competition_run_counts: number;
    total_fetch_run_counts: number;
    total_fetch_success_counts: number;
    total_fetch_failed_counts: number;
    total_updated_items_counts: number;
}

export interface BettingTipsStatisticsInterface {
    id: string
    type: string
    is_multiples: boolean
    Multiples: 'Yes' | 'No'
    range: string
    initial_bankroll: number
    bankroll_topups: number
    final_bankroll: number
    total: number
    won: number
    won_percentage: number
    gain: number
    roi: number
    longest_winning_streak: number
    longest_losing_streak: number
}

export interface BettingTipsTotalsInterface {
    total_totals: number;
    total_won: number;
    total_gain: number;
    average_roi: number;
}


export interface DashboardStatsMatchJobLogsInterface {

    historical_results: {
        'today': DashJobLogsInterface;
        'all': DashJobLogsInterface;
    };
    recent_results: {
        'today': DashJobLogsInterface;
        'all': DashJobLogsInterface;
    };
    shallow_fixtures: {
        'today': DashJobLogsInterface;
        'all': DashJobLogsInterface;
    };
    fixtures: {
        'today': DashJobLogsInterface;
        'all': DashJobLogsInterface;
    };
}
export interface DashboardStatsInterface {
    countries: {
        totals: number;
        with_competitions: number;
        without_competitions: number;
    };
    competitions: {
        totals: number;
        active: number;
        inactive: number;
    };
    odds_enabled_competitions: {
        totals: number;
        active: number;
        inactive: number;
    };
    seasons: {
        totals: number;
        active: number;
        inactive: number;
    };
    standings: {
        totals: number;
        active: number;
        inactive: number;
    };
    teams: {
        totals: number;
        past: number;
        upcoming: number;
    };
    matches: {
        totals: number;
        past: number;
        upcoming: number;
    };
    predictions: {
        totals: number;
        past: number;
        upcoming: number;
    };
    odds: {
        totals: number;
        past: number;
        upcoming: number;
    };
    users: {
        totals: number;
        active: number;
        inactive: number;
    };
    subscribed_users: {
        totals: number;
        active: number;
        inactive: number;
    };
    tipsters: {
        totals: number;
        active: number;
        inactive: number;
    };

    advanced_matches: {
        'today': MatchesInterface;
        'all': TodayMatchesInterface;
    };
    seasons_job_logs: {
        'today': DashJobLogsInterface;
        'all': DashJobLogsInterface;
    };
    standings_job_logs: {
        historical_results: {
            'today': DashJobLogsInterface;
            'all': DashJobLogsInterface;
        };
        recent_results: {
            'today': DashJobLogsInterface;
            'all': DashJobLogsInterface;
        };
    };

    matches_job_logs: DashboardStatsMatchJobLogsInterface

    match_job_logs: DashboardStatsMatchJobLogsInterface

    predictions_job_logs: {
        'today': DashJobLogsInterface;
        'all': DashJobLogsInterface;
    };
    train_predictions_job_logs: {
        'today': DashJobLogsInterface;
        'all': DashJobLogsInterface;
    };
    competition_statistics_logs: {
        'today': CompetitionPredictionStatsInterface;
        'all': CompetitionPredictionStatsInterface;
    }
    competition_prediction_statistics_logs: {
        'today': CompetitionPredictionStatsInterface;
        'all': CompetitionPredictionStatsInterface;
    }

    betting_tips_statistics_logs:
    {
        'today': BettingTipsStatsInterface[]
        'all': BettingTipsStatsInterface
    }

}

export interface MatchesInterface {
    totals: number;
    past: number;
    upcoming: number;
    with_full_time_results_only: number;
    with_half_and_time_results: number;
    without_results: number;
}

export interface TodayMatchesInterface extends MatchesInterface { }

export interface CompetitionStatsInterface {
    total_job_run_count: number;
    total_competition_run_counts: number;
    total_seasons_run_counts: number;
    total_games_run_counts: number;
}
export interface BettingTipsStatsInterface {
    total_job_run_count: number;
    total_competition_run_counts: number;
    total_types_run_counts: number;
    total_games_run_counts: number;
}

export interface TodayCompetitionStatsInterface extends CompetitionStatsInterface { }

export interface CompetitionPredictionStatsInterface extends CompetitionStatsInterface { }

export interface TodayCompetitionPredictionStatsInterface extends CompetitionStatsInterface { }
