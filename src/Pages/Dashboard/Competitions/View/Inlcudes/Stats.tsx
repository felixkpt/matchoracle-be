import React, { useEffect } from 'react';
import { ProgressBar } from 'react-bootstrap';
import NoContentMessage from '@/components/NoContentMessage';
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

    const statsUrl = `dashboard/competitions/view/${competition.id}/statistics?season_id=${selectedSeason ? selectedSeason?.id : ''}${appendFromToDates(useDate, fromToDates)}`;

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
                                    {renderProgressBar('Full time - Home Wins', data.ft_home_wins, data.ft_home_wins_percentage, 'success')}
                                    {renderProgressBar('Full time - Draws', data.ft_draws, data.ft_draws_percentage, 'secondary')}
                                    {renderProgressBar('Full time - Away Wins', data.ft_away_wins, data.ft_away_wins_percentage, 'primary')}
                                    {renderProgressBar('BTS Time - Yes', data.ft_gg, data.ft_gg_percentage, 'success')}
                                    {renderProgressBar('BTS Time - No', data.ft_ng, data.ft_ng_percentage, 'secondary')}
                                    {renderProgressBar('Over/Under 1.5 - Over', data.ft_over15, data.ft_over15_percentage, 'success')}
                                    {renderProgressBar('Over/Under 1.5 - Under', data.ft_under15, data.ft_under15_percentage, 'secondary')}
                                    {renderProgressBar('Over/Under 2.5 - Over', data.ft_over25, data.ft_over25_percentage, 'success')}
                                    {renderProgressBar('Over/Under 2.5 - Under', data.ft_under25, data.ft_under25_percentage, 'secondary')}
                                    {renderProgressBar('Over/Under 3.5 - Over', data.ft_over35, data.ft_over35_percentage, 'success')}
                                    {renderProgressBar('Over/Under 3.5 - Under', data.ft_under35, data.ft_under35_percentage, 'secondary')}
                                    {renderProgressBar('Half time - Home Wins', data.ht_home_wins, data.ht_home_wins_percentage, 'success')}
                                    {renderProgressBar('Half time - Draws', data.ht_draws, data.ht_draws_percentage, 'secondary')}
                                    {renderProgressBar('Half time - Away Wins', data.ht_away_wins, data.ht_away_wins_percentage, 'primary')}

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

export default Stats;
