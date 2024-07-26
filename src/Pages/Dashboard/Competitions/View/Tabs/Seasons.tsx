import { CompetitionTabInterface, SeasonsListInterface } from '@/interfaces/FootballInterface'
import CompetitionSubHeader from '../Inlcudes/CompetitionSubHeader';
import AutoTable from '@/components/Autos/AutoTable';
import GeneralModal from '@/components/Modals/GeneralModal';

interface Props extends CompetitionTabInterface, SeasonsListInterface { }

const Seasons: React.FC<Props> = ({ record, selectedSeason }) => {

  const competition = record

  const columns = [
    { key: 'id' },
    { key: 'competition.name' },
    { key: 'start_date' },
    { key: 'end_date' },
    { key: 'winner.name' },
    { label: 'Status', key: 'Status' },
    { key: 'Fetched_standings' },
    { key: 'Fetched_all_matches' },
    { key: 'Fetched_all_single_matches' },
    { key: 'Created_by' },
    { label: 'Created At', key: 'created_at' },
    { label: 'Action', key: 'action' },
  ]

  return (
    <div>
      {
        competition &&
        <div>
          <div className='shadow-sm'>
            <CompetitionSubHeader actionTitle="Fetch Seasons" actionButton={'fetchSeasons'} record={competition} />
          </div>
          <AutoTable key={selectedSeason?.id} columns={columns} baseUri={`dashboard/seasons?competition_id=${competition.id}`} search={true} tableId={'competitionSeasonsTable'} customModalId="teamModal" />

          {
            competition &&
            <>
              <GeneralModal title={`Seasons form`} actionUrl={`dashboard/competitions/view/${competition.id}/fetch-seasons`} size={'modal-lg'} id={`fetchSeasons`}>
                <div>
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