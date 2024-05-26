import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import Str from '@/utils/Str';
import useListSources from '@/hooks/list-sources/useListSources';
import AutoPageHeader from '@/components/Autos/AutoPageHeader';

const Index = () => {
  // begin component common config
  const pluralName = 'Permissions'
  const singularName = 'Permission'
  const uri = '/dashboard/settings/role-permissions/permissions'
  const componentId = Str.slug(pluralName)
  const [modelDetails, setModelDetails] = useState({})
  const search = true
  const columns = [
    {
      label: 'ID',
      key: 'id',
    },
    {
      label: 'Permission Name',
      key: 'name',
    },
    {
      label: 'Guard Name',
      key: 'guard_name',
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
  ]
  // end component common config

  const { rolePermissions: listSources } = useListSources();

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
          listSources={listSources}
        />
      </div>
      {
        modelDetails && <><AutoModal id={`${componentId}Modal`} modelDetails={modelDetails} actionUrl={uri} listSources={listSources} /></>
      }
    </div>
  );
};

export default Index;