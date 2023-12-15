import useAxios from '@/hooks/useAxios';
import React, { useEffect, useState } from 'react';
import { CompetitionInterface, CompetitionTabInterface, SeasonsListInterface } from '@/interfaces/FootballInterface';
import CompetitionHeader from '../Inlcudes/CompetitionHeader';
import GeneralModal from '@/components/Modals/GeneralModal';
import AsyncSeasonsList from '../Inlcudes/AsyncSeasonsList';
import StandingsTable from '@/components/Teams/StandingsTable';
import Loader from '@/components/Loader';
import DefaultMessage from '@/components/DefaultMessage';

interface Props extends CompetitionTabInterface, SeasonsListInterface {}

const Standings: React.FC<Props> = ({ record, seasons, selectedSeason, setSelectedSeason, setKey }) => {

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
                    <CompetitionHeader title="Standings" actionTitle="Fetch Standings" actionButton="fetchStandings" record={competition} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} />


                    {
                        !loading ?
                            <div>
                                {
                                    detailedCompetition && detailedCompetition.standings.length > 0 ?
                                        <StandingsTable standings={detailedCompetition.standings} />
                                        :
                                        <DefaultMessage />
                                }
                            </div>
                            :
                            <Loader />
                    }

                    <GeneralModal title={`Fetch Standings form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-standings`} size={'modal-lg'} id={`fetchStandings`} setKey={setKey}>
                        <div>

                            <div className="form-group mb-3">
                                <label htmlFor="season_id">Season</label>
                                <AsyncSeasonsList seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason}  />
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
