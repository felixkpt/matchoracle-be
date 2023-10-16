import useAxios from '@/hooks/useAxios';
import React, { useEffect, useState } from 'react';
import { CompetitionInterface, SeasonInterface, StandingInterface, StandingTableInterface } from '@/interfaces/CompetitionInterface';
import Loader from '@/components/Loader';

interface Props {
    record: CompetitionInterface | undefined;
    setKey: any
}

const Standings: React.FC<Props> = ({ record, setKey }) => {
    const competition = record

    const [selectedSeason, setSelectedSeason] = useState<string | null>(null);
    const [seasons, setSeasons] = useState<SeasonInterface[] | null>(null);
    const [detailedCompetition, setDetailedCompetition] = useState<CompetitionInterface | null>(null);
    const { get, loading } = useAxios<CompetitionInterface>();
    const { loading: loadingStandigs, post } = useAxios<CompetitionInterface>();

    useEffect(() => {
        if (competition) {
            get(`admin/competitions/view/${competition.id}/seasons`).then((res) => {
                if (res) {
                    setSeasons(res);
                    if (res.length > 0) {
                        // Set the first season as the default selected season
                        setSelectedSeason(res[0].id);
                    }
                }
            });
        }
    }, [competition]);

    useEffect(() => {
        if (competition && selectedSeason) {
            get(`admin/competitions/view/${competition.id}/standings/${selectedSeason}`).then((res) => {
                if (res) {
                    setDetailedCompetition(res);
                }
            });
        }
    }, [competition, selectedSeason]);


    const handleSeasonChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
        setSelectedSeason(event.target.value);
    };

    if (loading) {
        return <div>Loading...</div>;
    }

    if (!detailedCompetition) {
        return <div>No data available</div>;
    }

    const fetchStandings = () => {
        if (competition) {
            post(`admin/competitions/view/${competition.id}/fetch-standings`).then((res) => {
                if (res && setKey) {
                    setKey((k) => k+1)
                }
            })
        }
    }

    return (
        <div>
            <div className='d-flex justify-content-between position-relative'>

                <h4>Standings with Details</h4>
                {loadingStandigs ? <><Loader message='Fetching' /></> : ''}
                <button className='btn btn-sm btn-success' onClick={fetchStandings}>Fetch Standings</button>
            </div>

            <div>
                <label>Select Season:</label>
                <select onChange={handleSeasonChange} value={selectedSeason}>
                    {seasons.map((season) => (
                        <option key={season.id} value={season.id}>
                            {season.start_date} - {season.end_date}
                        </option>
                    ))}
                </select>
            </div>

            {detailedCompetition.standings.map((standing: StandingInterface) => (
                <div key={standing.id}>
                    <p>{standing.stage}</p>
                    <p>{standing.type}</p>
                    <div>
                        <table className="table table-striped table-hover shadow rounded">
                            <thead className="table-secondary">
                                <tr>
                                    <th>#Pos</th>
                                    <th>Name</th>
                                    <th>MP</th>
                                    <th>W</th>
                                    <th>D</th>
                                    <th>L</th>
                                    <th>GF</th>
                                    <th>GA</th>
                                    <th>GD</th>
                                    <th>Pts</th>
                                </tr>
                            </thead>
                            <tbody>
                                {standing.standing_table.map((teamStanding: StandingTableInterface) => (
                                    <tr key={teamStanding.id}>
                                        <td className='col-1 col-xl-1'><div className="p-1 bg-secondary rounded text-white d-flex col-12 col-xl-5">#{teamStanding.position}</div></td>
                                        <td>{teamStanding.team.name}</td>
                                        <td>{teamStanding.played_games}</td>
                                        <td>{teamStanding.won}</td>
                                        <td>{teamStanding.draw}</td>
                                        <td>{teamStanding.lost}</td>
                                        <td>{teamStanding.goals_for}</td>
                                        <td>{teamStanding.goals_against}</td>
                                        <td>{teamStanding.goal_difference}</td>
                                        <td>{teamStanding.points}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            ))}
        </div>
    );
};

export default Standings;
