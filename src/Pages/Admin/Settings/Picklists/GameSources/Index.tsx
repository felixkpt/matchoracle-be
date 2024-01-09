import AutoTable from '@/components/AutoTable';
import AutoModal from '@/components/AutoModal';
import { useState } from 'react';
import PageHeader from '@/components/PageHeader';

const Index = () => {

  const [modelDetails, setModelDetails] = useState({})

  return (
    <div>
      <div>
        <PageHeader title={'Game Sources list'} action="button" actionText="Create Game Source" actionTargetId="GameSources" permission='admin/settings/picklists/game-sources' />
        <div>
          <AutoTable
            baseUri='/admin/settings/picklists/game-sources'
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
                key: 'Created_at',
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
          />
        </div>
      </div>
      {
        modelDetails && <><AutoModal id={`GameSources`} modelDetails={modelDetails} actionUrl='/admin/settings/picklists/game-sources' /></>
      }
    </div>
  );
};

export default Index;