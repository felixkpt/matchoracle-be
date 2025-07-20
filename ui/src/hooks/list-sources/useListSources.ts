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
      const resp = await get('/dashboard/settings/role-permissions/roles' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []

    },

    async directPermissionsList(search?: string) {
      const resp = await get('/dashboard/settings/role-permissions/permissions' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []

    },

  }

  const posts = {

    async parentCategoryId(search?: string) {
      const resp = await get('/dashboard/posts/categories' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []

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
      const resp = await get('/dashboard/continents' + prepareParams(search)).then((response) => response.results || [])
      return resp?.data || []

    },
    async countryId(search?: string) {
      const resp = await get('/dashboard/countries' + prepareParams(search)).then((response) => response.results || [])
      return resp?.data || []

    },
    async nationalityId(search?: string) {
      const resp = await get('/dashboard/countries' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []
    },
    async addressId(search?: string) {
      const resp = await get('/dashboard/teams/addresses' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []
    },
    async venueId(search?: string) {
      const resp = await get('/dashboard/teams/venues' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []
    },
    async coachId(search?: string) {
      const resp = await get('/dashboard/teams/coaches' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []
    },
    async competitionId(search?: string) {
      const resp = await get('/dashboard/competitions' + prepareParams(search)).then((response) => response.results || [])
      return resp?.data || []
    },
    async teamId(search?: string) {
      const resp = await get('/dashboard/teams' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []
    },

  }

  const tips = {
    async bettingStrategyId(search?: string) {
      const resp = await get('/dashboard/settings/picklists/betting-strategies' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []
    },
    async subscriptionDurationId(search?: string) {
      const resp = await get('/dashboard/settings/picklists/subscription-duration' + prepareParams(search)).then((response) => response.results || [])
      return resp.results?.data || []
    },
    async advantagesList(search?: string) {
      const resp = await get('/dashboard/settings/picklists/betting-strategies-pro-cons' + prepareParams(`${search}&type=advantage`)).then((response) => response.results || [])
      return resp.results?.data || []
    },
    async disadvantagesList(search?: string) {
      const resp = await get('/dashboard/settings/picklists/betting-strategies-pro-cons' + prepareParams(`${search}&type=disadvantage`)).then((response) => response.results || [])
      return resp.results?.data || []
    },
  }

  return {
    rolePermissions,
    posts,
    competitions,
    tips,
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