import { CompetitionTabInterface, SeasonInterface, SeasonsListInterface } from '@/interfaces/FootballInterface'
import CompetitionHeader from '../Inlcudes/CompetitionHeader';
import AutoTable from '@/components/AutoTable';
import GeneralModal from '@/components/Modals/GeneralModal';
import AsyncSeasonsList from '../Inlcudes/AsyncSeasonsList';
import { useEffect, useState } from 'react';
import { appendFromToDates } from '@/utils/helpers';
import FormatDate from '@/utils/FormatDate';
import Loader from '@/components/Loader';

interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const UpcomingMatches: React.FC<Props> = ({ record, seasons, selectedSeason, setSelectedSeason, setKey }) => {

  const competition = record
  const [key, setLocalKey] = useState(0);
  const initialDates: Array<Date | string | undefined> = [FormatDate.YYYYMMDD(new Date()), undefined];
  const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>(initialDates);
  const [useDate, setUseDates] = useState(false);

  const [currentSeason, setCurrentSeason] = useState<SeasonInterface | null>(null);

  useEffect(() => {

    if (seasons && seasons.length > 0) {
      let current = seasons.find((itm) => itm.is_current == 1) || seasons[0]
      if (current) {
        setCurrentSeason(current)
      }
    }

  }, [seasons])

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
          <CompetitionHeader title="Upcoming Matches" actionTitle="Fetch Fixtures" actionButton="fetchUpcomingMatches" record={competition} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} setLocalKey={setLocalKey} hideSeasons={true} />

          {
            currentSeason
              ? <AutoTable key={key} columns={columns} baseUri={`admin/competitions/view/${competition.id}/matches?season_id=${currentSeason ? currentSeason?.id : ''}${appendFromToDates(useDate, fromToDates)}&type=upcoming`} search={true} tableId={'matchesTable'} customModalId="teamModal" />
              : <Loader />
          }

          {
            competition &&
            <>
              <GeneralModal title={`Fetch Fixtures form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-matches`} size={'modal-lg'} id={`fetchUpcomingMatches`} setKey={setKey}>
                <div>

                  <div className="form-group mb-3">
                    <label htmlFor="season_id">Season</label>
                    <AsyncSeasonsList seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} />
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