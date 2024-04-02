import { CompetitionTabInterface, SeasonsListInterface } from '@/interfaces/FootballInterface'
import CompetitionHeader from '../Inlcudes/CompetitionSubHeader';
import AutoTable from '@/components/AutoTable';
import GeneralModal from '@/components/Modals/GeneralModal';
import { useEffect, useState } from 'react';
import { appendFromToDates } from '@/utils/helpers';
import Str from '@/utils/Str';

interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const UpcomingMatches: React.FC<Props> = ({ record, seasons, selectedSeason }) => {

  const competition = record
  const [useDate, setUseDates] = useState(false);
  const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>([undefined, undefined]);


  const columns = [
    { key: 'ID' },
    { key: 'home_team.name' },
    { key: 'away_team.name' },
    { label: 'half_time', key: 'half_time' },
    { label: 'full_time', key: 'full_time' },
    { label: 'Status', key: 'Status' },
    { key: 'Created_by' },
    { key: 'utc_date' },
    { label: 'Last Fetch', key: 'Last_fetch' },
    { label: 'Action', key: 'action' },
  ]

  const [baseUri, setBaseUri] = useState('')

  useEffect(() => {

    if (competition) {
      let uri = `admin/competitions/view/${competition.id}/matches?type=upcoming&order_direction=asc`
      if (useDate) {
        uri = uri + `${appendFromToDates(useDate, fromToDates)}`
      }
      setBaseUri(uri)
    }
  }, [competition, fromToDates])

  return (
    <div>
      {
        competition &&
        <div>
          <CompetitionHeader title="Upcoming Matches" actionTitle="Fetch Upcoming Matches" actionButton="fetchUpcomingMatches" record={competition} seasons={seasons} selectedSeason={selectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} />

          {baseUri &&
            <AutoTable key={baseUri} columns={columns} baseUri={baseUri} search={true} tableId={'matchesTable'} customModalId="teamModal" />
          }

          {
            competition &&
            <>
              <GeneralModal title={`Fetch Fixtures form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-matches?is_fixtures=1`} size={'modal-lg'} id={`fetchUpcomingMatches`}>
                <div className='row align-items-center'>

                  <div className="form-group mb-3">
                    <label htmlFor="season_id">Season:&nbsp;</label>
                    <label>{seasons?.length && Str.before(seasons[0].start_date, '-')}</label>
                  </div>
                  <div className={`col-6 form-group mb-3`}>
                    <div className="form-check">
                      <input
                        className="form-check-input"
                        id='shallow_fetch'
                        type='checkbox'
                        name={`shallow_fetch`}
                        defaultChecked={true}
                      />
                      <label className="form-check-label" htmlFor={`shallow_fetch`}>
                        Shallow fetch
                      </label>
                    </div>
                  </div>
                  <div className="col form-group mb-3">
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