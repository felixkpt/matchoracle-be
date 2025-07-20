
import roles from '@/routes/dashboard/settings/role-permissions/roles/index'
import permissions from '@/routes/dashboard/settings/role-permissions/permissions/index'

const index = [

    {
        path: 'roles',
        children: roles,
    },
    {
        path: 'permissions',
        children: permissions,
    },
]

export default index