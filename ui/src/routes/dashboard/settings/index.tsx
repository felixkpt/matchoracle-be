
import picklists from '@/routes/dashboard/settings/picklists'
import rolePermissions from '@/routes/dashboard/settings/role-permissions'
import users from '@/routes/dashboard/settings/users';
import system from '@/routes/dashboard/settings/system/index';

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