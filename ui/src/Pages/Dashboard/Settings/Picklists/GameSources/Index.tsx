import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import PageHeader from '@/components/PageHeader';
import { ModelDetailsInterface } from '@/interfaces/UncategorizedInterfaces';

const Index = () => {

  const [modelDetails, setModelDetails] = useState<ModelDetailsInterface>()

  return (
    <div>
      <div>
        <PageHeader title={'Game Sources list'} action="button" actionText="Create Game Source" actionTargetId="GameSources" permission='dashboard/settings/picklists/game-sources' />
        <div>
          <AutoTable
            baseUri='/dashboard/settings/picklists/game-sources'
            columns={[
              {
                label: 'ID',
                key: 'id',
              },
              {
                label: 'Name',
                key: 'name',
              },
              {
                label: 'Priority NO',
                key: 'priority_number',
              },
              { key: 'Created_by' },
              {
                label: 'Created At',
                key: 'created_at',
              },
              {
                label: 'Status',
                key: 'Status',
              },
              {
                label: 'Action',
                key: 'action',
              },
            ]}
            getModelDetails={setModelDetails}
            search={true}
            tableId='gamesourcesTable'
          />
        </div>
      </div>
      {
        modelDetails && <><AutoModal id={`GameSources`} modelDetails={modelDetails} actionUrl='/dashboard/settings/picklists/game-sources' /></>
      }
    </div>
  );
};

export default Index;