
import picklists from '@/routes/admin/settings/picklists'
import rolePermissions from '@/routes/admin/settings/role-permissions'
import users from '@/routes/admin/settings/users';
import system from '@/routes/admin/settings/system/index';

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
  },{
    path: 'system',
    children: system,
  },
]

export default index