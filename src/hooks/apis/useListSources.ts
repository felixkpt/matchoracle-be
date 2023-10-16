import { ListSourceInterface } from '@/interfaces/UncategorizedInterfaces';
import useAxios from '../useAxios'

const useListSources = (params?: string) => {

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

    async rolesList(q?: string) {
      const res = await get('admin/settings/role-permissions/roles?&' + params + '&q=' + q).then((res) => res.data || [])
      return res || []

    },

    async directPermissionsList(q?: string) {
      const res = await get('admin/settings/role-permissions/permissions?&' + params + '&q=' + q).then((res) => res.data || [])
      return res || []

    },

  }

  const posts = {

    async parentCategoryId(q?: string) {
      const res = await get('/admin/posts/categories?all=1&' + params + '&q=' + q).then((res) => res.data || [])
      return res || []

    },

  }

  const booleanOptions: ListSourceInterface[] = [
    {
      id: '1',
      name: 'Yes',
    },
    {
      id: '0',
      name: 'No',
    }
  ]

  const competitions = {

    async hasCompetitions(q?: string) {
      return booleanOptions
    },
    async hasTeams(q?: string) {
      return booleanOptions
    },
    async continentId(q?: string) {
      const res = await get('/admin/continents?' + params + '&q=' + q).then((res) => res.data || [])
      return res || []

    },
    async countryId(q?: string) {
      const res = await get('/admin/countries?' + params + '&q=' + q).then((res) => res.data || [])
      return res || []

    },


  }

  return {
    rolePermissions,
    posts,
    competitions
  }
}

export default useListSources