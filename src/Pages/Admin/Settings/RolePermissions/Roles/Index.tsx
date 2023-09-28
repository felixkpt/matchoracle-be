import AutoTable from '@/components/AutoTable';
import AutoModal from '@/components/AutoModal';
import { useState } from 'react';
import PageHeader from '@/components/PageHeader';
import useListSources from '@/hooks/apis/useListSources';

const Index = () => {
  const [modelDetails, setModelDetails] = useState({})

  const { rolePermissions: list_sources } = useListSources()

  return (
    <div>
      <PageHeader title={'Roles List'} action="button" actionText="Create role" actionTargetId="AutoModal" permission='admin.settings.role-permissions.roles' />
      <AutoTable
        baseUri='/admin/settings/role-permissions/roles'
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
          {
            label: 'Created At',
            key: 'created_at',
          },
          {
            label: 'Action',
            key: 'action',
          },
        ]}
        getModelDetails={setModelDetails}
        search={true}
        list_sources={list_sources}
      />
      {
        modelDetails && <><AutoModal modelDetails={modelDetails} actionUrl='/admin/settings/role-permissions/roles' list_sources={list_sources} /></>
      }
    </div>
  );
};

export default Index;

