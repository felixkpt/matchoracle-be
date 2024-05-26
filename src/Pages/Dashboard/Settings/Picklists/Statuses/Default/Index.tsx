import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import Str from '@/utils/Str';
import AutoPageHeader from '@/components/Autos/AutoPageHeader';

const Index = () => {
  // begin component common config
  const pluralName = 'Statuses'
  const singularName = 'Status'
  const uri = '/dashboard/settings/picklists/statuses/default'
  const componentId = Str.slug(pluralName)
  const [modelDetails, setModelDetails] = useState({})
  const search = true
  const columns = [
    {
      label: 'ID',
      key: 'id',
    },
    {
      label: 'Status Name',
      key: 'name',
    },
    {
      label: 'Icon',
      key: 'Icon',
    },
    {
      label: 'Class',
      key: 'class',
    },
    { key: 'Created_by' },
    {
      label: 'Created At',
      key: 'Created_at',
    },
    {
      label: 'Action',
      key: 'action',
    },
  ]
  // end component common config

  return (
    <div>
      <div>
        <AutoPageHeader pluralName={pluralName} singularName={singularName} componentId={componentId} />
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