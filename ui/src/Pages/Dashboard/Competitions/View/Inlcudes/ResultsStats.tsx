import { useEffect, useState } from 'react';
import { ProgressBar } from 'react-bootstrap';
import NoContentMessage from '@/components/NoContentMessage';
import useAxios from '@/hooks/useAxios';
import Loader from '@/components/Loader';
import { CompetitionInterface, SeasonInterface } from '@/interfaces/FootballInterface';

interface StatsData {
    season_counts: number;
    ft_counts: number;
    ft_home_wins: number;
    ft_home_wins_percentage: number;
    ft_draws: number;
    ft_draws_percentage: number;
    ft_away_wins: number;
    ft_away_wins_percentage: number;
    ft_gg: number;
    ft_gg_percentage: number;
    ft_ng: number;
    ft_ng_percentage: number;
    ft_over15: number;
    ft_over15_percentage: number;
    ft_under15: number;
    ft_under15_percentage: number;
    ft_over25: number;
    ft_over25_percentage: number;
    ft_under25: number;
    ft_under25_percentage: number;
    ft_over35: number;
    ft_over35_percentage: number;
    ft_under35: number;
    ft_under35_percentage: number;

    ht_counts: number;
    ht_home_wins: number;
    ht_home_wins_percentage: number;
    ht_draws: number;
    ht_draws_percentage: number;
    ht_away_wins: number;
    ht_away_wins_percentage: number;
    ht_gg: number;
    ht_gg_percentage: number;
    ht_ng: number;
    ht_ng_percentage: number;
    ht_over15: number;
    ht_over15_percentage: number;
    ht_under15: number;
    ht_under15_percentage: number;
    ht_over25: number;
    ht_over25_percentage: number;
    ht_under25: number;
    ht_under25_percentage: number;
    ht_over35: number;
    ht_over35_percentage: number;
    ht_under35: number;
    ht_under35_percentage: number;
}

type Props = {
    competition: CompetitionInterface;
    selectedSeason?: SeasonInterface | null;
};

const ResultsStats = ({ competition, selectedSeason }: Props) => {
    const { loading, get } = useAxios();

    const statsUrl = `dashboard/competitions/view/${competition.id}/results-statistics?season_id=${selectedSeason ? selectedSeason?.id : ''}`;

    const [data, setData] = useState<StatsData | null>(null);

    useEffect(() => {
        get(statsUrl).then((response) => {
            const results = response.results;
            if (results) {
                setData(results);
            }
        });
    }, [statsUrl]);


    const renderSection = (
        counts: number,
        sectionTitle: string,
        progressBars: Array<{ label: string; value: number; percentage: number; variant: string }>
    ) => (
        <div className="col-12 mb-4">
            <div className="card">
                <div className="card-header">
                    <h6 className="d-flex justify-content-between align-items-center">
                        <span>{sectionTitle}</span>
                        <span className="fs-6 fw-bold">{`Total: ${counts}`}</span>
                    </h6>
                </div>
                <div className="card-body">
                    {progressBars.map(({ label, value, percentage, variant }, index) => (
                        <div className="row mb-3" key={index}>
                            <div className="col-12 d-flex justify-content-between">
                                <span className="fs-6">{label}</span>
                                <span className="fs-6 text-success">{`${value}/${counts}`}</span>
                            </div>
                            <div className="col-12">
                                <ProgressBar variant={variant} now={percentage} label={`${percentage}%`} />
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );

    return (
        <div>
            <div className="card">
                <div className="card-header">
                    <h5 className="row justify-content-between">
                        <span className='col-md-6 col-xl-5 mb-2 mb-md-0 text-dark'>Results stats</span>
                        <span className='col-md-6 col-xl-7 text-xl-end text-success row'>
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
                    {loading ? (
                        <Loader />
                    ) : data ? (
                        <>
                            <div>
                                <h6 className='text-black-50'>Full Time</h6>
                                {renderSection(data.ft_counts, "Home/Draw/Away Wins", [
                                    { label: "Home Wins", value: data.ft_home_wins, percentage: data.ft_home_wins_percentage, variant: "success" },
                                    { label: "Draws", value: data.ft_draws, percentage: data.ft_draws_percentage, variant: "secondary" },
                                    { label: "Away Wins", value: data.ft_away_wins, percentage: data.ft_away_wins_percentage, variant: "primary" },
                                ])}
                                {renderSection(data.ft_counts, "BTS (Both Teams to Score)", [
                                    { label: "Yes", value: data.ft_gg, percentage: data.ft_gg_percentage, variant: "success" },
                                    { label: "No", value: data.ft_ng, percentage: data.ft_ng_percentage, variant: "secondary" },
                                ])}
                                {renderSection(data.ft_counts, "Over/Under 1.5", [
                                    { label: "Over", value: data.ft_over15, percentage: data.ft_over15_percentage, variant: "success" },
                                    { label: "Under", value: data.ft_under15, percentage: data.ft_under15_percentage, variant: "secondary" },
                                ])}
                                {renderSection(data.ft_counts, "Over/Under 2.5", [
                                    { label: "Over", value: data.ft_over25, percentage: data.ft_over25_percentage, variant: "success" },
                                    { label: "Under", value: data.ft_under25, percentage: data.ft_under25_percentage, variant: "secondary" },
                                ])}
                                {renderSection(data.ft_counts, "Over/Under 3.5", [
                                    { label: "Over", value: data.ft_over35, percentage: data.ft_over35_percentage, variant: "success" },
                                    { label: "Under", value: data.ft_under35, percentage: data.ft_under35_percentage, variant: "secondary" },
                                ])}
                            </div>
                            <div>
                                <h6 className='text-black-50'>Half Time</h6>
                                {renderSection(data.ht_counts, "Home/Draw/Away Wins", [
                                    { label: "Home Wins", value: data.ht_home_wins, percentage: data.ht_home_wins_percentage, variant: "success" },
                                    { label: "Draws", value: data.ht_draws, percentage: data.ht_draws_percentage, variant: "secondary" },
                                    { label: "Away Wins", value: data.ht_away_wins, percentage: data.ht_away_wins_percentage, variant: "primary" },
                                ])}
                                {renderSection(data.ht_counts, "BTS (Both Teams to Score)", [
                                    { label: "Yes", value: data.ht_gg, percentage: data.ht_gg_percentage, variant: "success" },
                                    { label: "No", value: data.ht_ng, percentage: data.ht_ng_percentage, variant: "secondary" },
                                ])}
                                {renderSection(data.ht_counts, "Over/Under 1.5", [
                                    { label: "Over", value: data.ht_over15, percentage: data.ht_over15_percentage, variant: "success" },
                                    { label: "Under", value: data.ht_under15, percentage: data.ht_under15_percentage, variant: "secondary" },
                                ])}
                                {renderSection(data.ht_counts, "Over/Under 2.5", [
                                    { label: "Over", value: data.ht_over25, percentage: data.ht_over25_percentage, variant: "success" },
                                    { label: "Under", value: data.ht_under25, percentage: data.ht_under25_percentage, variant: "secondary" },
                                ])}
                                {renderSection(data.ht_counts, "Over/Under 3.5", [
                                    { label: "Over", value: data.ht_over35, percentage: data.ht_over35_percentage, variant: "success" },
                                    { label: "Under", value: data.ht_under35, percentage: data.ht_under35_percentage, variant: "secondary" },
                                ])}
                            </div>
                            <div className="col-12 mt-4">
                                <h6 className="d-flex gap-2 justify-content-between">
                                    <div className='opacity-0'>n/a</div>
                                </h6>
                                <p className="mt-3 d-flex gap-2 justify-content-between text-muted">
                                    <span className="pro-style opacity-0">n/a</span>
                                    <span className="pro-style opacity-0">n/a</span>
                                </p>
                            </div>

                        </>
                    ) : (
                        <NoContentMessage />
                    )
                    }
                </div>
            </div>
        </div>
    );
};

export default ResultsStats;
