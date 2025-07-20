import Permissions from '../../../../../Pages/Dashboard/Settings/RolePermissions/Permissions/Index';
import DefaultLayout from '../../../../../Layouts/Default/DefaultLayout';

const relativeUri = 'dashboard/settings/role-permissions/permissions/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Permissions} />,
    },
];

export default index;
