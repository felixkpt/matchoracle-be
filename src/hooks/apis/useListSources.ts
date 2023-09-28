import { ListSourceInterface } from '@/interfaces/UncategorizedInterfaces';
import useAxios from '../useAxios'

const useListSources = () => {

  const { get } = useAxios()

  const rolePermissions = {

    guardName: async () => {
      return [
        {
          id: 'web',
          name: 'web',
        },
        {
          id: 'api',
          name: 'api',
        }
      ] as ListSourceInterface[];
    },

    async rolesList() {
      const res = await get('admin/settings/role-permissions/roles?all=1').then((res) => res)
      return res || []

    },

    async directPermissionsList() {
      const res = await get('admin/settings/role-permissions/permissions?all=1').then((res) => res)
      return res || []

    }
  }

  return {
    rolePermissions
  }
}

export default useListSources