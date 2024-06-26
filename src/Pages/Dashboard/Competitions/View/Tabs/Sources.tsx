import AddSource from '@/components/AddSource';
import Loader from '@/components/Loader';
import { CompetitionInterface } from '@/interfaces/FootballInterface';
import { publish } from '@/utils/events';
import CompetitionSubHeader from '../Inlcudes/CompetitionSubHeader';

interface Props {
  record: CompetitionInterface | undefined
}

const Sources: React.FC<Props> = ({ record }) => {

  const competition = record

  return (
    <div>
      <div className='shadow-sm'>
        <CompetitionSubHeader actionTitle="Fetch Results" actionButton="fetchPastMatches" record={competition} />
      </div>

      <div className="card mt-3">
        <div className="card-body">
          {
            competition ?

              <form encType="" method="post" id='addSources' action-url={`dashboard/competitions/view/${competition.id}/add-sources`} onSubmit={(e: any) => publish('ajaxPost', e)} >
                <AddSource record={competition} hideClose={true} />
              </form>
              :
              <Loader />
          }
        </div>
      </div>
    </div>
  );
};

export default Sources;

