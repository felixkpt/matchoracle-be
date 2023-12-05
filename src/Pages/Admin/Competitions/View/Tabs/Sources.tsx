import AddSource from '@/components/AddSource';
import Loader from '@/components/Loader';
import PageHeader from '@/components/PageHeader';
import { CompetitionInterface } from '@/interfaces/FootballInterface';
import { publish } from '@/utils/events';

interface Props {
  record: CompetitionInterface | undefined;
}

const Sources: React.FC<Props> = ({ record }) => {
  const competition = record

  return (
    <div>
      <PageHeader title='Sources' />

      <div className="card">
        <div className="card-body">
          {
            competition ?

              <form encType="" method="post" id='addSources' action-url={`admin/competitions/view/${competition.id}/add-sources`} onSubmit={(e: any) => publish('ajaxPost', e)} >
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

