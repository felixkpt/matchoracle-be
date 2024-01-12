import Roles from '@/Pages/Admin/Settings/RolePermissions/Roles/Index';
import Role from '@/Pages/Admin/Settings/RolePermissions/Roles/View/Index';
import AuthenticatedLayout from '@/Layouts/Authenicated/AuthenticatedLayout';

const relativeUri = 'admin/settings/role-permissions/roles/';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Roles} />,
    },
    {
        path: 'view/:id',
        element: <AuthenticatedLayout uri={relativeUri + 'view/:id'} permission="" Component={Role} />,
    },
];

export default index;
