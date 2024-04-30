import Permissions from '../../../../../Pages/Dashboard/Settings/RolePermissions/Permissions/Index';
import CreateOrUpdatePermission from '../../../../../Pages/Dashboard/Settings/RolePermissions/Permissions/CreateOrUpdatePermission';
import DefaultLayout from '../../../../../Layouts/Default/DefaultLayout';

const relativeUri = 'dashboard/settings/role-permissions/permissions/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Permissions} />,
    },
    {
        path: 'create',
        element: <DefaultLayout uri={relativeUri + 'create'} permission="" Component={CreateOrUpdatePermission} />,
    },
    {
        path: 'view/:id/edit',
        element: <DefaultLayout uri={relativeUri + 'view/:id/edit'} permission="" Component={CreateOrUpdatePermission} />,
    },
];

export default index;
