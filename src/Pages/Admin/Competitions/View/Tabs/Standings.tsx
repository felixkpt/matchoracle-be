import useAxios from '@/hooks/useAxios';
import React, { useEffect, useState } from 'react';
import { CompetitionInterface, CompetitionTabInterface, StandingInterface, StandingTableInterface } from '@/interfaces/FootballInterface';
import CompetitionHeader from '../Inlcudes/CompetitionHeader';
import GeneralModal from '@/components/Modals/GeneralModal';
import AsyncSeasonsList from '../Inlcudes/AsyncSeasonsList';
import StandingsTable from '@/components/StandingsTable';


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
                    {detailedCompetition &&
                        <StandingsTable standings={detailedCompetition.standings} />
                    }

                    <GeneralModal title={`Fetch Standings form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-standings`} size={'modal-lg'} id={`fetchStandings`} setKey={setKey}>
                        <div>

                            <div className="form-group mb-3">
                                <label htmlFor="season_id">Season</label>
                                <AsyncSeasonsList record={competition} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} />
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
