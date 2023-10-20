import AddSource from '@/components/AddSource';
import { CompetitionInterface } from '@/interfaces/CompetitionInterface';
import { publish } from '@/utils/events';

interface Props {
  record: CompetitionInterface | undefined;
}

const Sources: React.FC<Props> = ({ record }) => {
  const competition = record

  if (!competition) return null

  return (
    <div className='mt-4'>
      <form encType="" method="post" id='addSources' action-url={`admin/competitions/view/${competition.id}/add-sources`} onSubmit={(e: any) => publish('ajaxPost', e)} >
        <AddSource record={competition} hideClose={true} />
      </form>
    </div>
  );
};

export default Sources;

