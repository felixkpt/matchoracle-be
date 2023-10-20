import useAxios from '@/hooks/useAxios';
import React, { useEffect, useState } from 'react';
import { CompetitionInterface, CompetitionTabInterface, StandingInterface, StandingTableInterface } from '@/interfaces/CompetitionInterface';
import CompetitionHeader from '../Inlcudes/CompetitionHeader';
import GeneralModal from '@/components/Modals/GeneralModal';
import AsyncSeasonsList from '../Inlcudes/AsyncSeasonsList';


const Standings: React.FC<CompetitionTabInterface> = ({ record, selectedSeason, setSelectedSeason, setKey }) => {
    const competition = record

    const [detailedCompetition, setDetailedCompetition] = useState<CompetitionInterface | null>(null);

    const { get, loading, data } = useAxios();

    useEffect(() => {
        if (competition && selectedSeason) {
            get(`admin/competitions/view/${competition.id}/standings/${selectedSeason?.id}`).then((res) => {
                if (res) {
                    setDetailedCompetition(res);
                }
            });
        }
    }, [competition, selectedSeason]);

    return (
        <div>
            {
                competition
                &&
                <div>
                    <CompetitionHeader title="Standings" actionTitle="Fetch Standings" actionButton="fetchStandings" record={competition} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} />

                    {detailedCompetition && detailedCompetition.standings.map((standing: StandingInterface) => (
                        <div key={standing.id}>
                            <p>{standing.stage}</p>
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

                    <GeneralModal title={`Fetch Standings form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-standings`} size={'modal-lg'} id={`fetchStandings`} setKey={setKey}>
                        <div>

                            <div className="form-group mb-3">
                                <label htmlFor="season_id">Season</label>
                                <AsyncSeasonsList record={competition} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason}  />
                            </div>
                            <div className="form-group mb-3">
                                <label htmlFor="matchday">Match day</label>
                                <input type="number" min={0} max={200} name='matchday' id='matchday' className='form-control' />
                            </div>
                            <div className="modal-footer gap-1">
                                <button type="button" className="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" className="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </GeneralModal>
                </div>

            }
        </div>
    );
};

export default Standings;
