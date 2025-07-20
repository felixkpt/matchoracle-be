import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import Str from '@/utils/Str';
import { ModelDetailsInterface } from '@/interfaces/UncategorizedInterfaces';

const Index = () => {
  // begin component common config
  const pluralName = 'Competition Prediction Logs'
  const uri = '/dashboard/competitions/prediction-logs'
  const componentId = Str.slug(pluralName)
  const [modelDetails, setModelDetails] = useState<ModelDetailsInterface>()
  const search = true
  const columns = [
    {
      label: 'ID',
      key: 'id',
    },
    {
      label: 'Competition',
      key: 'competition.name',
    },
    {
      label: 'Date',
      key: 'date',
    },
    {
      key: 'total_games',
    },
    {
      key: 'predictable_games',
    },
    {
      key: 'predicted_games',
    },
    {
      key: 'unpredicted_games',
    },
    { key: 'Created_by' },
    {
      label: 'Created At',
      key: 'created_at',
    },
    {
      label: 'Action',
      key: 'action',
    },
  ]
  // end component common config

  return (
    <div>
      <h3>{pluralName} List</h3>
      <div>
        <AutoTable
          baseUri={uri}
          columns={columns}
          getModelDetails={setModelDetails}
          search={search}
          tableId={`${componentId}Table`}
        />
      </div>
      {
        modelDetails && <><AutoModal id={`${componentId}Modal`} modelDetails={modelDetails} actionUrl={uri} /></>
      }
    </div>
  );
};

export default Index;