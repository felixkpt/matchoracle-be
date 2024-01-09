import { CompetitionTabInterface, SeasonsListInterface } from '@/interfaces/FootballInterface'
import CompetitionHeader from '../Inlcudes/CompetitionSubHeader';
import AutoTable from '@/components/AutoTable';
import GeneralModal from '@/components/Modals/GeneralModal';

interface Props extends CompetitionTabInterface, SeasonsListInterface {}

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
    { label: 'Created At', key: 'Created_at' },
    { label: 'Action', key: 'action' },
  ]

  return (
    <div>
      {
        competition &&
        <div>
          <CompetitionHeader title="Seasons" actionTitle="Fetch Seasons" actionButton={'fetchSeasons'} record={competition} />

          <AutoTable key={selectedSeason?.id} columns={columns} baseUri={`admin/seasons?competition_id=${competition.id}`} search={true} tableId={'seasonsTable'} customModalId="teamModal" />

          {
            competition &&
            <>
              <GeneralModal title={`Seasons form`} actionUrl={`admin/competitions/view/${competition.id}/fetch-seasons`} size={'modal-lg'} id={`fetchSeasons`}>
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