export interface CountryInterface {
    id: string;
    name: string;
    slug: string;
    code?: string | null;
    dial_code?: string | null;
    flag?: string | null;
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
    crest: string;
    address_id: string | null;
    website: string | null;
    founded: string | null;
    club_colors: string | null;
    venue_id: string | null;
    coach_id: string | null;
    coach_contract: any;
    competition_id: string;
    competitions: CompetitionInterface[];
    continent_id: string;
    country_id: string | null;
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
    emblem: string | null;
    plan: string | null;
    abbreviation: string | null;
    has_teams: boolean | null;
    continent_id: string;
    country_id: string | null;
    country: CountryInterface;
    priority_number: number;
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
    hda: string;
    home_win_proba: number;
    draw_proba: number;
    away_win_proba: number;
    gg: string;
    gg_proba: number;
    ng_proba: number;
    over25: string;
    over25_proba: number;
    under25_proba: number;
    cs: string;
    cs_proba: number;
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
    current_user_votes: boolean,
    prediction: PredictionInterface,
    formatted_prediction: PredictionInterface,
    CS: string;

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
    uri: string
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
    setKey?: React.Dispatch<React.SetStateAction<number>>;
    useDate?: boolean;
    isDisabled?: boolean
}


export interface SeasonsListInterface {
    seasons: SeasonInterface[] | null
    selectedSeason: SeasonInterface | null
    setSelectedSeason: React.Dispatch<React.SetStateAction<SeasonInterface | null>>;
 
}
