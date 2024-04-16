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
      const res = await get('admin/settings/role-permissions/roles' + prepareParams(q)).then((res) => res.data || [])
      return res || []

    },

    async directPermissionsList(q?: string) {
      const res = await get('admin/settings/role-permissions/permissions' + prepareParams(q)).then((res) => res.data || [])
      return res || []

    },

  }

  const posts = {

    async parentCategoryId(q?: string) {
      const res = await get('/admin/posts/categories' + prepareParams(q)).then((res) => res.data || [])
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
      const res = await get('/admin/continents' + prepareParams(q)).then((res) => res.data || [])
      return res || []

    },
    async countryId(q?: string) {
      const res = await get('/admin/countries' + prepareParams(q)).then((res) => res.data || [])
      return res || []

    },
    async nationalityId(q?: string) {
      const res = await get('/admin/countries' + prepareParams(q)).then((res) => res.data || [])
      return res || []
    },
    async addressId(q?: string) {
      const res = await get('/admin/teams/addresses' + prepareParams(q)).then((res) => res.data || [])
      return res || []
    },
    async venueId(q?: string) {
      const res = await get('/admin/teams/venues' + prepareParams(q)).then((res) => res.data || [])
      return res || []
    },
    async coachId(q?: string) {
      const res = await get('/admin/teams/coaches' + prepareParams(q)).then((res) => res.data || [])
      return res || []
    },
    async competitionId(q?: string) {
      console.log('should trigger this function!')
      const res = await get('/admin/competitions' + prepareParams(q)).then((res) => res.data || [])
      return res || []
    },
    async teamId(q?: string) {
      const res = await get('/admin/teams' + prepareParams(q)).then((res) => res.data || [])
      return res || []
    },

  }

  return {
    rolePermissions,
    posts,
    competitions
  }

  function prepareParams(q: string | undefined) {
    let query

    if (params)
      query = params + '&q=' + q
    else
      query = '?q=' + q

    return query
  }

}


export default useListSources