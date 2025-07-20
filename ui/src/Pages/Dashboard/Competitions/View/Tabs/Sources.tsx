import AddSource from '@/components/AddSource';
import Loader from '@/components/Loader';
import { CompetitionInterface } from '@/interfaces/FootballInterface';
import { publish } from '@/utils/events';

interface Props {
  record: CompetitionInterface | undefined
}

const Sources: React.FC<Props> = ({ record }) => {

  const competition = record

  return (
    <div>
      <div className="card mt-3">
        <div className="card-body">
          {
            competition ?

              <form encType="" method="post" id='addSources' data-action={`dashboard/competitions/view/${competition.id}/add-sources`} onSubmit={(e: any) => publish('autoPost', e)} >
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

