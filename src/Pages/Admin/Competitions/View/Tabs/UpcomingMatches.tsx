import { CompetitionTabInterface } from '@/interfaces/FootballInterface'
import CompetitionHeader from '../Inlcudes/CompetitionHeader';
import AutoTable from '@/components/AutoTable';
import Str from '@/utils/Str';
import GeneralModal from '@/components/Modals/GeneralModal';
import AsyncSeasonsList from '../Inlcudes/AsyncSeasonsList';
import { useState } from 'react';

const UpcomingMatches: React.FC<CompetitionTabInterface> = ({ record, selectedSeason, setSelectedSeason, setKey }) => {

  const competition = record
  const [key, setLocalKey] = useState(0);  
  const [startDate, setStartDate] = useState(null);
  const [useDate, setUseDate] = useState(false);

  const columns = [
    { key: 'ID' },
    { key: 'home_team.name' },
    { key: 'away_team.name' },
    { label: 'half_time', key: 'half_time' },
    { label: 'full_time', key: 'full_time' },
    { label: 'Status', key: 'Status' },
    { label: 'User', key: 'user_id' },
    { key: 'utc_date' },
    { label: 'Created At', key: 'Created_at' },
    { label: 'Action', key: 'action' },
  ]

  return (
    <div>
      {
        competition &&
        <div>
          <CompetitionHeader title="Upcoming Matches" actionTitle="Fetch Fixtures" actionButton="fetchUpcomingMatches" record={competition} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} startDate={startDate} setStartDate={setStartDate} setUseDate={setUseDate} setLocalKey={setLocalKey} />

          <AutoTable key={key} columns={columns} baseUri={`admin/competitions/view/${competition.id}/matches?season_id=${selectedSeason ? selectedSeason?.id : ''}&type=upcoming&date=${useDate ? startDate : ''}`} search={true} tableId={'matchesTable'} customModalId="teamModal" />

          {
            competition &&
            <>
              <GeneralModal title={`Fetch Fixtures form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-matches`} size={'modal-lg'} id={`fetchUpcomingMatches`} setKey={setKey}>
                <div>

                  <div className="form-group mb-3">
                    <label htmlFor="season_id">Season</label>
                    <AsyncSeasonsList record={competition} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} useDate={useDate} />
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
            </>
          }
        </div>
      }

    </div>
  )
}

export default UpcomingMatches