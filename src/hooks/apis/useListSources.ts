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

    async rolesList(search?: string) {
      const res = await get('admin/settings/role-permissions/roles' + prepareParams(search)).then((res) => res.data || [])
      return res || []

    },

    async directPermissionsList(search?: string) {
      const res = await get('admin/settings/role-permissions/permissions' + prepareParams(search)).then((res) => res.data || [])
      return res || []

    },

  }

  const posts = {

    async parentCategoryId(search?: string) {
      const res = await get('/admin/posts/categories' + prepareParams(search)).then((res) => res.data || [])
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

    async hasCompetitions() {
      return booleanOptions
    },
    async hasTeams() {
      return booleanOptions
    },
    async continentId(search?: string) {
      const res = await get('/admin/continents' + prepareParams(search)).then((res) => res.data || [])
      return res || []

    },
    async countryId(search?: string) {
      const res = await get('/admin/countries' + prepareParams(search)).then((res) => res.data || [])
      return res || []

    },
    async nationalityId(search?: string) {
      const res = await get('/admin/countries' + prepareParams(search)).then((res) => res.data || [])
      return res || []
    },
    async addressId(search?: string) {
      const res = await get('/admin/teams/addresses' + prepareParams(search)).then((res) => res.data || [])
      return res || []
    },
    async venueId(search?: string) {
      const res = await get('/admin/teams/venues' + prepareParams(search)).then((res) => res.data || [])
      return res || []
    },
    async coachId(search?: string) {
      const res = await get('/admin/teams/coaches' + prepareParams(search)).then((res) => res.data || [])
      return res || []
    },
    async competitionId(search?: string) {
      console.log('should trigger this function!')
      const res = await get('/admin/competitions' + prepareParams(search)).then((res) => res.data || [])
      return res || []
    },
    async teamId(search: string) {
      const res = await get('/admin/teams' + prepareParams(search)).then((res) => res.data || [])
      return res || []
    },

  }

  return {
    rolePermissions,
    posts,
    competitions
  }

  function prepareParams(search: string | undefined) {
    let query

    if (params)
      query = params + '&search=' + search
    else
      query = '?search=' + search

    return query
  }

}


export default useListSources