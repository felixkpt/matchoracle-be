import AutoTable from '@/components/Autos/AutoTable';
import AutoModal from '@/components/Autos/AutoModal';
import { useState } from 'react';
import PageHeader from '@/components/PageHeader';
import useListSources from '@/hooks/apis/useListSources';
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';

const Index = () => {
  const [modelDetails, setModelDetails] = useState<CollectionItemsInterface>()

  const { rolePermissions: listSources } = useListSources()

  return (
    <div>
      <PageHeader title={'Roles List'} action="button" actionText="Create role" actionTargetId="AutoModal" permission='dashboard.settings.role-permissions.roles' />
      <AutoTable
        baseUri='/dashboard/settings/role-permissions/roles'
        columns={[
          {
            label: 'ID',
            key: 'id',
          },
          {
            label: 'Role Name',
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
      />
      {
        modelDetails && <><AutoModal modelDetails={modelDetails} actionUrl='/dashboard/settings/role-permissions/roles' listSources={listSources} /></>
      }
    </div>
  );
};

export default Index;

