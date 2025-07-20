import Roles from '../../../../../Pages/Dashboard/Settings/RolePermissions/Roles/Index';
import Role from '../../../../../Pages/Dashboard/Settings/RolePermissions/Roles/View/Index';
import DefaultLayout from '../../../../../Layouts/Default/DefaultLayout';

const relativeUri = 'dashboard/settings/role-permissions/roles/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Roles} />,
    },
    {
        path: 'view/:id',
        element: <DefaultLayout uri={relativeUri + 'view/:id'} permission="" Component={Role} />,
    },
];

export default index;
