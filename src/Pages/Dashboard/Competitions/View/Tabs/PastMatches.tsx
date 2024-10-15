import { CompetitionTabInterface, SeasonsListInterface } from '@/interfaces/FootballInterface'
import AutoTable from '@/components/Autos/AutoTable';
import GeneralModal from '@/components/Modals/GeneralModal';
import { useEffect, useState } from 'react';
import { appendFromToDates } from '@/utils/helpers';
import Str from '@/utils/Str';
import CompetitionSubHeader from '../Inlcudes/CompetitionSubHeader';
import { renderMatchViewlink } from '@/components/HtmlRenderers';
import { ActionsType } from '@/interfaces/UncategorizedInterfaces';

interface Props extends CompetitionTabInterface, SeasonsListInterface { }


const PastMatches: React.FC<Props> = ({ record, seasons, selectedSeason }) => {

  const competition = record

  const [useDate, setUseDates] = useState(false);
  const [fromToDates, setFromToDates] = useState<Array<Date | string | undefined>>([undefined, undefined]);

  const [baseUri, setBaseUri] = useState('')

  useEffect(() => {

    if (competition) {
      let uri = `dashboard/competitions/view/${competition.id}/matches?type=past`
      if (useDate) {
        uri = uri + `${appendFromToDates(useDate, fromToDates)}`
      } else {
        uri = uri + `&season_id=${selectedSeason ? selectedSeason?.id : ''}`
      }
      setBaseUri(uri)
    }
  }, [competition, fromToDates])

  const columns = [
    {
      key: 'id',
      renderCell: renderMatchViewlink
    },
    { key: 'home_team.name' },
    { key: 'away_team.name' },
    { label: 'half_time', key: 'half_time' },
    { label: 'full_time', key: 'full_time' },
    { label: 'Status', key: 'Status' },
    { key: 'Created_by' },
    { key: 'utc_date' },
    { label: 'Updated', key: 'Updated_at' },
    { label: 'Action', key: 'action' },
  ]

  const actions: ActionsType = {
    view: {
      actionMode: 'navigation'
    },
  }


  return (
    <div>
      {
        competition &&
        <div>
          <div className='shadow-sm'>
            <CompetitionSubHeader actionTitle="Fetch Results" actionButton="fetchPastMatches" record={competition} seasons={seasons} selectedSeason={selectedSeason} fromToDates={fromToDates} setFromToDates={setFromToDates} setUseDates={setUseDates} />
          </div>

          {baseUri &&
            <AutoTable key={baseUri} columns={columns} actions={actions} baseUri={baseUri} search={true} tableId={'competitionPastMatchesTable'} customModalId="teamModal" />
          }

          <GeneralModal title={`Fetch Results form`} actionUrl={`dashboard/competitions/view/${competition.id}/fetch-matches`} size={'modal-lg'} id={`fetchPastMatches`}>
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
            <div className={`form-group mb-3${selectedSeason ? (selectedSeason.is_current ? '' : ' d-none ') : ''}`}>
              <div className="form-check">
                <input
                  key={selectedSeason ? selectedSeason.id : ''}
                  className="form-check-input"
                  id='shallow_fetch'
                  type='checkbox'
                  name={`shallow_fetch`}
                  defaultChecked={selectedSeason ? !!selectedSeason.is_current : false}
                />
                <label className="form-check-label" htmlFor={`shallow_fetch`}>
                  Shallow fetch
                </label>
              </div>
            </div>
            <div className="form-group mb-3 d-none">
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