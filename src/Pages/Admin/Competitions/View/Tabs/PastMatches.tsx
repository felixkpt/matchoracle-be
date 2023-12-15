import { CompetitionTabInterface, SeasonsListInterface } from '@/interfaces/FootballInterface'
import CompetitionHeader from '../Inlcudes/CompetitionHeader';
import AutoTable from '@/components/AutoTable';
import GeneralModal from '@/components/Modals/GeneralModal';
import AsyncSeasonsList from '../Inlcudes/AsyncSeasonsList';
import { useState } from 'react';
import FormatDate from '@/utils/FormatDate';
import { appendFromToDates } from '@/utils/helpers';

interface Props extends CompetitionTabInterface, SeasonsListInterface {}

const PastMatches: React.FC<Props> = ({ record, seasons, selectedSeason, setSelectedSeason, setKey }) => {

  const competition = record
  const [key, setLocalKey] = useState(0);
  const initialDates: Array<Date | string | undefined> = [FormatDate.YYYYMMDD(new Date()), undefined];
  const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>(initialDates);
  const [useDate, setUseDates] = useState(false);

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
          <CompetitionHeader title="Played Matches" actionTitle="Fetch Results" actionButton="fetchPastMatches" record={competition} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} setLocalKey={setLocalKey} />

          <AutoTable key={key} columns={columns} baseUri={`admin/competitions/view/${competition.id}/matches?season_id=${selectedSeason ? selectedSeason?.id : ''}${appendFromToDates(useDate, fromToDates)}&type=past`} search={true} tableId={'matchesTable'} customModalId="teamModal" />

          <GeneralModal title={`Fetch Results form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-matches`} size={'modal-lg'} id={`fetchPastMatches`} setKey={setKey}>
            <div className="form-group mb-3">
              <label htmlFor="season_id">Season</label>
              <AsyncSeasonsList key={key} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} />
            </div>
            <div className="form-group mb-3">
              <label htmlFor="matchday">Match day</label>
              <input type="number" min={0} max={200} name='matchday' id='matchday' className='form-control' />
            </div>
            <div className="modal-footer gap-1">
              <button type="button" className="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="submit" className="btn btn-primary">Submit</button>
            </div>
          </GeneralModal>
        </div>
      }

    </div>
  )
}

export default PastMatches