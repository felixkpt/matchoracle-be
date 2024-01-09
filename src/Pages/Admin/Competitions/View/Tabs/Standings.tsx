import useAxios from '@/hooks/useAxios';
import React, { useEffect, useState } from 'react';
import { CompetitionInterface, CompetitionTabInterface } from '@/interfaces/FootballInterface';
import CompetitionSubHeader from '../Inlcudes/CompetitionSubHeader';
import GeneralModal from '@/components/Modals/GeneralModal';
import AsyncSeasonsList from '../Inlcudes/AsyncSeasonsList';
import StandingsTable from '@/components/Teams/StandingsTable';
import Loader from '@/components/Loader';
import DefaultMessage from '@/components/DefaultMessage';
import Str from '@/utils/Str';

const Standings: React.FC<CompetitionTabInterface> = ({ record, seasons, selectedSeason }) => {

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
                    <CompetitionSubHeader title="Standings" actionTitle="Fetch Standings" actionButton="fetchStandings" record={competition} seasons={seasons} selectedSeason={selectedSeason} />


                    {
                        !loading ?
                            <div key={selectedSeason?.id}>
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

                    <GeneralModal title={`Fetch Standings form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-standings`} size={'modal-lg'} id={`fetchStandings`}>
                        <div>
                            <div className="form-group mb-3">
                                <label htmlFor="season_id">Selected season {
                                    selectedSeason
                                    &&
                                    <span>
                                        {`${Str.before(selectedSeason.start_date, '-')} / ${Str.before(selectedSeason.end_date, '-')}`}
                                    </span>
                                } </label>
                                <input type="hidden" name="season_id" key={selectedSeason?.id} value={selectedSeason?.id} />
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
