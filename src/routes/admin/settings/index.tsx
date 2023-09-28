
import picklists from '@/routes/admin/settings/picklists'
import rolePermissions from '@/routes/admin/settings/role-permissions'
import users from '@/routes/admin/settings/users';

const index = [
  {
    path: 'users',
    children: users,
  },

  {
    path: 'picklists',
    children: picklists,
  },
  {
    path: 'role-permissions',
    children: rolePermissions,
  },
]

export default index