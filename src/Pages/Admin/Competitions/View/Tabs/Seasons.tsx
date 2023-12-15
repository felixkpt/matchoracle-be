import { CompetitionTabInterface, SeasonsListInterface } from '@/interfaces/FootballInterface'
import CompetitionHeader from '../Inlcudes/CompetitionHeader';
import AutoTable from '@/components/AutoTable';
import GeneralModal from '@/components/Modals/GeneralModal';
import AsyncSeasonsList from '../Inlcudes/AsyncSeasonsList';

interface Props extends CompetitionTabInterface, SeasonsListInterface {}

const Seasons: React.FC<Props> = ({ record, seasons, selectedSeason, setSelectedSeason, setKey }) => {

  const competition = record

  const columns = [
    { key: 'competition.name' },
    { key: 'start_date' },
    { key: 'end_date' },
    { label: 'Matchday', key: 'current_matchday' },
    { key: 'played' },
    { key: 'winner.name' },
    { label: 'Status', key: 'Status' },
    { label: 'User', key: 'user_id' },
    { label: 'Created At', key: 'Created_at' },
    { label: 'Action', key: 'action' },
  ]

  return (
    <div>
      {
        competition &&
        <div>
          <CompetitionHeader title="Seasons" actionTitle="Fetch Seasons" actionButton={'fetchSeasons'} seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} />

          <AutoTable key={selectedSeason?.id} columns={columns} baseUri={`admin/seasons?competition_id=${competition.id}&id=${selectedSeason?.id}`} search={true} tableId={'seasonsTable'} customModalId="teamModal" />

          {
            competition &&
            <>
              <GeneralModal title={`Seasons form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-seasons`} size={'modal-lg'} id={`fetchSeasons`} setKey={setKey}>
                <div>
                  <div className="form-group mb-3">
                    <label htmlFor="season_id">Season</label>
                    <AsyncSeasonsList seasons={seasons} selectedSeason={selectedSeason} setSelectedSeason={setSelectedSeason} />
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

export default Seasons