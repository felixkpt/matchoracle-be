import React, { useEffect } from 'react';
import { ProgressBar } from 'react-bootstrap';
import NoContentMessage from '@/components/NoContentMessage';
import Loader from '@/components/Loader';
import useAxios from '@/hooks/useAxios';
import { CompetitionInterface, SeasonInterface } from '@/interfaces/FootballInterface';
import { appendFromToDates } from "@/utils/helpers";
import { useLocation } from 'react-router-dom';

type Props = {
    competition: CompetitionInterface;
    selectedSeason: SeasonInterface;
    fromToDates: Array<Date | string | undefined>;
    useDate: any;
};

const PredictionsStats = ({ competition, selectedSeason, fromToDates, useDate }: Props) => {
    const { data, loading: loadingPreds, errors: errorsPreds, get: getPreds } = useAxios();

    const location = useLocation();
    const queryParams = new URLSearchParams(location.search);
    const predictionTypeId = queryParams.get('prediction_type_id');

    const predsStatsUrl = `admin/competitions/view/${competition.id}/prediction-statistics?season_id=${selectedSeason ? selectedSeason?.id : ''
        }${appendFromToDates(useDate, fromToDates)}&prediction_type_id=${predictionTypeId || ''}`;

    useEffect(() => {
        getPreds(predsStatsUrl);
    }, [predsStatsUrl]);

    const renderProgressBar = (label: string, value: number, preds: number, preds_true: number, preds_true_percentage: number) => (
        <div key={label} className="col-12 mb-4">
            <div className="card">
                <div className="card-header">
                    <h6 className="d-flex justify-content-between align-items-center">
                        <span className='text-primary'>{label}</span>
                        <span className="fs-6 fw-bold">{`${value}/${data.counts}`}</span>
                    </h6>
                </div>
                <div className="card-body">
                    <div className="col-12 d-flex justify-content-between">
                        <span>
                            <span className='me-1 text-dark'>Predictions</span> <span style={{ fontSize: '1rem', color: 'orange' }}>{preds}</span>
                        </span>
                        <span>
                            <span className='me-1 text-dark'>Correct</span> <span className='text-success'>{preds_true}</span>
                        </span>
                    </div>
                    <div className="col-12">
                        <ProgressBar variant="success" now={preds_true_percentage} label={`${preds_true_percentage}%`} />
                    </div>
                </div>
            </div>
        </div>
    );

    return (
        <div>
            <div className="card">
                <div className="card-header">
                    <h5 className="d-flex gap-2 justify-content-between">
                        <div className='text-primary'>Prediction stats</div>
                        <div className='text-success'>{`Total matches: ${(data && data.counts) ? data.counts : 0}`}</div>
                    </h5>
                </div>
                <div className="card-body">
                    {!loadingPreds ? (
                        <div>
                            {data ? (
                                <div className="row">
                                    {renderProgressBar('Full Time Home Wins', data.ft_home_wins_counts, data.ft_home_wins_preds, data.ft_home_wins_preds_true, data.ft_home_wins_preds_true_percentage)}
                                    {renderProgressBar('Full Time Draws', data.ft_draws_counts, data.ft_draws_preds, data.ft_draws_preds_true, data.ft_draws_preds_true_percentage)}
                                    {renderProgressBar('Full Time Away Wins', data.ft_away_wins_counts, data.ft_away_wins_preds, data.ft_away_wins_preds_true, data.ft_away_wins_preds_true_percentage)}
                                    {renderProgressBar('Full Time BTS - Yes', data.ft_gg_counts, data.ft_gg_preds, data.ft_gg_preds_true, data.ft_gg_preds_true_percentage)}
                                    {renderProgressBar('Full Time BTS - No', data.ft_ng_counts, data.ft_ng_preds, data.ft_ng_preds_true, data.ft_ng_preds_true_percentage)}
                                    {renderProgressBar('Full Time Over 1.5', data.ft_over15_counts, data.ft_over15_preds, data.ft_over15_preds_true, data.ft_over15_preds_true_percentage)}
                                    {renderProgressBar('Full Time Under 1.5', data.ft_under15_counts, data.ft_under15_preds, data.ft_under15_preds_true, data.ft_under15_preds_true_percentage)}
                                    {renderProgressBar('Full Time Over 2.5', data.ft_over25_counts, data.ft_over25_preds, data.ft_over25_preds_true, data.ft_over25_preds_true_percentage)}
                                    {renderProgressBar('Full Time Under 2.5', data.ft_under25_counts, data.ft_under25_preds, data.ft_under25_preds_true, data.ft_under25_preds_true_percentage)}
                                    {renderProgressBar('Full Time Over 3.5', data.ft_over35_counts, data.ft_over35_preds, data.ft_over35_preds_true, data.ft_over35_preds_true_percentage)}
                                    {renderProgressBar('Full Time Under 3.5', data.ft_under35_counts, data.ft_under35_preds, data.ft_under35_preds_true, data.ft_under35_preds_true_percentage)}

                                    <div className="col-12 mt-4">
                                        <h6 className="d-flex gap-2 justify-content-between">
                                            <div>{`${(data.average_score) ? 'Average Score: ' + data.average_score : '0'}%`}</div>
                                        </h6>
                                        <p className="mt-3 d-flex gap-2 justify-content-between text-muted">
                                            <span className="pro-style">Last trained: {(competition.Predictions_last_train)}</span>
                                            <span className="pro-style">Trained to: {competition.predictions_trained_to}</span>
                                        </p>
                                    </div>
                                </div>
                            ) : (
                                <NoContentMessage />
                            )}
                        </div>
                    ) : (
                        <Loader />
                    )}
                </div>
            </div>
        </div>
    );
};

export default PredictionsStats;
