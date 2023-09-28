
import roles from '@/routes/admin/settings/role-permissions/roles/index'
import permissions from '@/routes/admin/settings/role-permissions/permissions/index'

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