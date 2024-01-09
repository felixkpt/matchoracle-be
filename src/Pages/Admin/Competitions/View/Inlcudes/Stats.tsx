import React, { useEffect } from 'react';
import { ProgressBar } from 'react-bootstrap';
import DefaultMessage from '@/components/DefaultMessage';
import useAxios from '@/hooks/useAxios';
import { CompetitionInterface, SeasonInterface } from '@/interfaces/FootballInterface';
import { appendFromToDates } from '@/utils/helpers';
import Loader from '@/components/Loader';
import Str from '@/utils/Str';

type Props = {
    competition: CompetitionInterface;
    selectedSeason: SeasonInterface;
    fromToDates: Array<Date | string | undefined>;
    useDate: any;
};

const Stats = ({ competition, selectedSeason, fromToDates, useDate }: Props) => {
    const { data, loading, errors, get } = useAxios();

    const statsUrl = `admin/competitions/view/${competition.id}/statistics?season_id=${selectedSeason ? selectedSeason?.id : ''}${appendFromToDates(useDate, fromToDates)}`;

    useEffect(() => {
        get(statsUrl);
    }, [statsUrl]);

    const renderProgressBar = (label: string, value: number, percentage: number, variant: string) => (
        <div className="col-12 mb-4">
            <div className="card">
                <div className="card-header">
                    <h6 className="d-flex justify-content-between align-items-center">
                        <span>{label}</span>
                        <span className="fs-6 fw-bold">{`${value}/${data.counts}`}</span>
                    </h6>
                </div>
                <div className="card-body">
                    {data && data.counts ? (
                        <div className="row gap-1">
                            <div className="col-12 row">
                                <div className="col-12 d-flex justify-content-between">
                                    <span className="fs-6">{Str.afterFirst(label, ' - ')}</span>
                                    <span className="fs-6 text-success">{value}</span>
                                </div>
                                <div className="col-12">
                                    <ProgressBar variant={variant} now={percentage} label={`${percentage}%`} />
                                </div>
                            </div>
                        </div>
                    ) : (
                        ' N/A'
                    )}
                </div>
            </div>
        </div>
    );

    return (
        <div>
            <div className="card">
                <div className="card-header">
                    <h5 className="d-flex gap-2 justify-content-between">
                        <div>Results stats</div>
                        <div>{`Total matches: ${(data && data.counts) ? data.counts : 0}`}</div>
                    </h5>
                </div>
                <div className="card-body">
                    {!loading ? (
                        <div>
                            {data ? (
                                <div className="row">
                                    {renderProgressBar('Full time - Home Wins', data.full_time_home_wins, data.full_time_home_wins_percentage, 'success')}
                                    {renderProgressBar('Full time - Draws', data.full_time_draws, data.full_time_draws_percentage, 'secondary')}
                                    {renderProgressBar('Full time - Away Wins', data.full_time_away_wins, data.full_time_away_wins_percentage, 'primary')}
                                    {renderProgressBar('BTS Time - Yes', data.gg, data.gg_percentage, 'success')}
                                    {renderProgressBar('BTS Time - No', data.ng, data.ng_percentage, 'secondary')}
                                    {renderProgressBar('Over/Under 1.5 - Over', data.over15, data.over15_percentage, 'success')}
                                    {renderProgressBar('Over/Under 1.5 - Under', data.under15, data.under15_percentage, 'secondary')}
                                    {renderProgressBar('Over/Under 2.5 - Over', data.over25, data.over25_percentage, 'success')}
                                    {renderProgressBar('Over/Under 2.5 - Under', data.under25, data.under25_percentage, 'secondary')}
                                    {renderProgressBar('Over/Under 3.5 - Over', data.over35, data.over35_percentage, 'success')}
                                    {renderProgressBar('Over/Under 3.5 - Under', data.under35, data.under35_percentage, 'secondary')}
                                    {renderProgressBar('Half time - Home Wins', data.half_time_home_wins, data.half_time_home_wins_percentage, 'success')}
                                    {renderProgressBar('Half time - Draws', data.half_time_draws, data.half_time_draws_percentage, 'secondary')}
                                    {renderProgressBar('Half time - Away Wins', data.half_time_away_wins, data.half_time_away_wins_percentage, 'primary')}
                                    
                                </div>
                            ) : (
                                <DefaultMessage />
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

export default Stats;
