import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import PageHeader from '@/components/PageHeader';
import useListSources from '@/hooks/apis/useListSources';

const Index = () => {

  const [modelDetails, setModelDetails] = useState({})

  const { rolePermissions: listSources } = useListSources();

  return (
    <div>
      <PageHeader title={'Permissions List'} action="button" actionText="Create permission" actionTargetId="CreatePermission" permission='/dashboard/settings/role-permissions/permissions' />
      <AutoTable
        baseUri='/dashboard/settings/role-permissions/permissions'
        columns={[
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
        ]}
        getModelDetails={setModelDetails}
        search={true}
        listSources={listSources}
        tableId='permissionsTable'
      />
      {
        modelDetails && <><AutoModal id={`CreatePermission`} modelDetails={modelDetails} actionUrl='/dashboard/settings/role-permissions/permissions' listSources={listSources} /></>
      }
    </div>
  );
};

export default Index;

