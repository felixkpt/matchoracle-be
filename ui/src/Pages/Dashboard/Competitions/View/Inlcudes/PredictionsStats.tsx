import { useEffect, useState } from 'react';
import { ProgressBar } from 'react-bootstrap';
import NoContentMessage from '@/components/NoContentMessage';
import Loader from '@/components/Loader';
import useAxios from '@/hooks/useAxios';
import { CompetitionInterface, SeasonInterface } from '@/interfaces/FootballInterface';
import { useLocation } from 'react-router-dom';

interface PredictionsStatsData {
    season_counts: number;
    ft_counts: number;
    ft_home_wins_preds: number;
    ft_home_wins_preds_true: number;
    ft_home_wins_preds_true_percentage: number;
    ft_draws_preds: number;
    ft_draws_preds_true: number;
    ft_draws_preds_true_percentage: number;
    ft_away_wins_preds: number;
    ft_away_wins_preds_true: number;
    ft_away_wins_preds_true_percentage: number;
    ft_gg_preds: number;
    ft_gg_preds_true: number;
    ft_gg_preds_true_percentage: number;
    ft_ng_preds: number;
    ft_ng_preds_true: number;
    ft_ng_preds_true_percentage: number;
    ft_over15_preds: number;
    ft_over15_preds_true: number;
    ft_over15_preds_true_percentage: number;
    ft_under15_preds: number;
    ft_under15_preds_true: number;
    ft_under15_preds_true_percentage: number;
    ft_over25_preds: number;
    ft_over25_preds_true: number;
    ft_over25_preds_true_percentage: number;
    ft_under25_preds: number;
    ft_under25_preds_true: number;
    ft_under25_preds_true_percentage: number;
    ft_over35_preds: number;
    ft_over35_preds_true: number;
    ft_over35_preds_true_percentage: number;
    ft_under35_preds: number;
    ft_under35_preds_true: number;
    ft_under35_preds_true_percentage: number;

    ft_home_wins_counts: number;
    ft_draws_counts: number;
    ft_away_wins_counts: number;
    ft_gg_counts: number;
    ft_ng_counts: number;
    ft_over15_counts: number;
    ft_under15_counts: number;
    ft_over25_counts: number;
    ft_under25_counts: number;
    ft_over35_counts: number;
    ft_under35_counts: number;

    ht_counts: number;
    ht_home_wins_preds: number;
    ht_home_wins_preds_true: number;
    ht_home_wins_preds_true_percentage: number;
    ht_draws_preds: number;
    ht_draws_preds_true: number;
    ht_draws_preds_true_percentage: number;
    ht_away_wins_preds: number;
    ht_away_wins_preds_true: number;
    ht_away_wins_preds_true_percentage: number;
    ht_gg_preds: number;
    ht_gg_preds_true: number;
    ht_gg_preds_true_percentage: number;
    ht_ng_preds: number;
    ht_ng_preds_true: number;
    ht_ng_preds_true_percentage: number;
    ht_over15_preds: number;
    ht_over15_preds_true: number;
    ht_over15_preds_true_percentage: number;
    ht_under15_preds: number;
    ht_under15_preds_true: number;
    ht_under15_preds_true_percentage: number;
    ht_over25_preds: number;
    ht_over25_preds_true: number;
    ht_over25_preds_true_percentage: number;
    ht_under25_preds: number;
    ht_under25_preds_true: number;
    ht_under25_preds_true_percentage: number;
    ht_over35_preds: number;
    ht_over35_preds_true: number;
    ht_over35_preds_true_percentage: number;
    ht_under35_preds: number;
    ht_under35_preds_true: number;
    ht_under35_preds_true_percentage: number;

    ht_home_wins_counts: number;
    ht_draws_counts: number;
    ht_away_wins_counts: number;
    ht_gg_counts: number;
    ht_ng_counts: number;
    ht_over15_counts: number;
    ht_under15_counts: number;
    ht_over25_counts: number;
    ht_under25_counts: number;
    ht_over35_counts: number;
    ht_under35_counts: number;

    average_score: number | null;
}

type Props = {
    competition: CompetitionInterface;
    selectedSeason?: SeasonInterface | null;
};

const PredictionsStats = ({ competition, selectedSeason }: Props) => {
    const { loading: loadingPreds, get: getPreds } = useAxios();
    const location = useLocation();
    const queryParams = new URLSearchParams(location.search);
    const predictionTypeId = queryParams.get('prediction_type_id');

    const predsStatsUrl = `dashboard/competitions/view/${competition.id}/prediction-statistics?season_id=${selectedSeason ? selectedSeason?.id : ''
        }&prediction_type_id=${predictionTypeId || ''}`;

    const [data, setData] = useState<PredictionsStatsData | null>(null);

    useEffect(() => {
        getPreds(predsStatsUrl).then((response) => {
            const results = response.results;
            if (results) setData(results);
        });
    }, [predsStatsUrl]);

    const renderSection = (
        counts: number,
        sectionTitle: string,
        stats: Array<{ label: string; counts: number; preds: number; predsTrue: number; predsTruePercentage: number }>) => (
        <div className="card mb-4">
            <div className="card-header">
                <h6 className="d-flex justify-content-between align-items-center text-info-emphasis">
                    <span>{sectionTitle}</span>
                    <span className="fs-6 fw-bold">{`Total: ${counts}`}</span>
                </h6>
            </div>
            <div className="card-body">
                {stats.map(({ label, preds, predsTrue, predsTruePercentage }, index) =>
                    <div className="row mb-3" key={index}>

                        <div className="col-12 d-flex justify-content-between">
                            <span>
                                <span className='me-1 text-dark'>{label}, predictions</span> <span style={{ fontSize: '1rem', color: 'orange' }}>{preds}</span>
                            </span>
                            <span>
                                <span className='me-1 text-dark'>Correct</span> <span className='text-success'>{predsTrue}</span>
                            </span>
                        </div>
                        <div className="col-12">
                            <ProgressBar variant="success" now={predsTruePercentage} label={`${predsTruePercentage}%`} />
                        </div>
                    </div>
                )}
            </div>
        </div>
    );

    return (
        <div>
            <div className="card mb-4">
                <div className="card-header">
                    <h5 className="row justify-content-between">
                        <span className='col-md-6 col-xl-5 mb-2 mb-md-0 text-dark'>Prediction stats</span>
                        <span className='col-md-6 col-xl-7 text-xl-end text-warning row'>
                            <span>{`Total matches: ${data?.ft_counts ?? 0}`}</span>
                            {
                                data?.ft_counts
                                &&
                                <span><small className='text-muted'>({data?.season_counts || 1} {`${data?.season_counts > 1 ? 'seasons' : 'season'}`})</small></span>

                            }            
                        </span>
                    </h5>
                </div>
                <div className="card-body">
                    {loadingPreds ? (
                        <Loader />
                    ) : data ? (
                        <>
                            <div>
                                <h6 className='text-black-50'>Full Time</h6>
                                {renderSection(data.ft_counts, 'Home/Draw/Away', [
                                    { label: 'Home Wins', counts: data.ft_home_wins_counts, preds: data.ft_home_wins_preds, predsTrue: data.ft_home_wins_preds_true, predsTruePercentage: data.ft_home_wins_preds_true_percentage },
                                    { label: 'Draws', counts: data.ft_draws_counts, preds: data.ft_draws_preds, predsTrue: data.ft_draws_preds_true, predsTruePercentage: data.ft_draws_preds_true_percentage },
                                    { label: 'Away Wins', counts: data.ft_away_wins_counts, preds: data.ft_away_wins_preds, predsTrue: data.ft_away_wins_preds_true, predsTruePercentage: data.ft_away_wins_preds_true_percentage },
                                ])}
                                {renderSection(data.ft_counts, 'BTTS (Both Teams to Score)', [
                                    { label: 'Yes', counts: data.ft_gg_counts, preds: data.ft_gg_preds, predsTrue: data.ft_gg_preds_true, predsTruePercentage: data.ft_gg_preds_true_percentage },
                                    { label: 'No', counts: data.ft_ng_counts, preds: data.ft_ng_preds, predsTrue: data.ft_ng_preds_true, predsTruePercentage: data.ft_ng_preds_true_percentage },
                                ])}
                                {renderSection(data.ft_counts, 'Over/Under 1.5', [
                                    { label: 'Over 1.5', counts: data.ft_over15_counts, preds: data.ft_over15_preds, predsTrue: data.ft_over15_preds_true, predsTruePercentage: data.ft_over15_preds_true_percentage },
                                    { label: 'Under 1.5', counts: data.ft_under15_counts, preds: data.ft_under15_preds, predsTrue: data.ft_under15_preds_true, predsTruePercentage: data.ft_under15_preds_true_percentage },
                                ])}
                                {renderSection(data.ft_counts, 'Over/Under 2.5', [
                                    { label: 'Over 2.5', counts: data.ft_over25_counts, preds: data.ft_over25_preds, predsTrue: data.ft_over25_preds_true, predsTruePercentage: data.ft_over25_preds_true_percentage },
                                    { label: 'Under 2.5', counts: data.ft_under25_counts, preds: data.ft_under25_preds, predsTrue: data.ft_under25_preds_true, predsTruePercentage: data.ft_under25_preds_true_percentage },
                                ])}
                                {renderSection(data.ft_counts, 'Over/Under 3.5', [
                                    { label: 'Over 3.5', counts: data.ft_over35_counts, preds: data.ft_over35_preds, predsTrue: data.ft_over35_preds_true, predsTruePercentage: data.ft_over35_preds_true_percentage },
                                    { label: 'Under 3.5', counts: data.ft_under35_counts, preds: data.ft_under35_preds, predsTrue: data.ft_under35_preds_true, predsTruePercentage: data.ft_under35_preds_true_percentage },
                                ])}
                            </div>
                            <div>
                                <h6 className='text-black-50'>Half Time</h6>
                                {renderSection(data.ht_counts, 'Home/Draw/Away', [
                                    { label: 'Home Wins', counts: data.ht_home_wins_counts, preds: data.ht_home_wins_preds, predsTrue: data.ht_home_wins_preds_true, predsTruePercentage: data.ht_home_wins_preds_true_percentage },
                                    { label: 'Draws', counts: data.ht_draws_counts, preds: data.ht_draws_preds, predsTrue: data.ht_draws_preds_true, predsTruePercentage: data.ht_draws_preds_true_percentage },
                                    { label: 'Away Wins', counts: data.ht_away_wins_counts, preds: data.ht_away_wins_preds, predsTrue: data.ht_away_wins_preds_true, predsTruePercentage: data.ht_away_wins_preds_true_percentage },
                                ])}
                                {renderSection(data.ht_counts, 'BTTS (Both Teams to Score)', [
                                    { label: 'Yes', counts: data.ht_gg_counts, preds: data.ht_gg_preds, predsTrue: data.ht_gg_preds_true, predsTruePercentage: data.ht_gg_preds_true_percentage },
                                    { label: 'No', counts: data.ht_ng_counts, preds: data.ht_ng_preds, predsTrue: data.ht_ng_preds_true, predsTruePercentage: data.ht_ng_preds_true_percentage },
                                ])}
                                {renderSection(data.ht_counts, 'Over/Under 1.5', [
                                    { label: 'Over 1.5', counts: data.ht_over15_counts, preds: data.ht_over15_preds, predsTrue: data.ht_over15_preds_true, predsTruePercentage: data.ht_over15_preds_true_percentage },
                                    { label: 'Under 1.5', counts: data.ht_under15_counts, preds: data.ht_under15_preds, predsTrue: data.ht_under15_preds_true, predsTruePercentage: data.ht_under15_preds_true_percentage },
                                ])}
                                {renderSection(data.ht_counts, 'Over/Under 2.5', [
                                    { label: 'Over 2.5', counts: data.ht_over25_counts, preds: data.ht_over25_preds, predsTrue: data.ht_over25_preds_true, predsTruePercentage: data.ht_over25_preds_true_percentage },
                                    { label: 'Under 2.5', counts: data.ht_under25_counts, preds: data.ht_under25_preds, predsTrue: data.ht_under25_preds_true, predsTruePercentage: data.ht_under25_preds_true_percentage },
                                ])}
                                {renderSection(data.ht_counts, 'Over/Under 3.5', [
                                    { label: 'Over 3.5', counts: data.ht_over35_counts, preds: data.ht_over35_preds, predsTrue: data.ht_over35_preds_true, predsTruePercentage: data.ht_over35_preds_true_percentage },
                                    { label: 'Under 3.5', counts: data.ht_under35_counts, preds: data.ht_under35_preds, predsTrue: data.ht_under35_preds_true, predsTruePercentage: data.ht_under35_preds_true_percentage },
                                ])}
                            </div>
                            <div className="col-12 mt-4">
                                <h6 className="d-flex gap-2 justify-content-between">
                                    <div>{`${(data.average_score) ? 'Average Score: ' + data.average_score : '0'}%`}</div>
                                </h6>
                                <p className="mt-3 d-flex gap-2 justify-content-between text-muted">
                                    <span className="pro-style">Last trained: {(competition.Predictions_last_train)}</span>
                                    <span className="pro-style">Trained to: {competition.last_action.predictions_trained_to}</span>
                                </p>
                            </div>
                        </>
                    ) : (
                        <NoContentMessage />
                    )}
                </div>
            </div>
        </div>
    );
};

export default PredictionsStats;
