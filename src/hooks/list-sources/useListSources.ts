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
      const res = await get('/dashboard/settings/role-permissions/roles' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []

    },

    async directPermissionsList(search?: string) {
      const res = await get('/dashboard/settings/role-permissions/permissions' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []

    },

  }

  const posts = {

    async parentCategoryId(search?: string) {
      const res = await get('/dashboard/posts/categories' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []

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
      const res = await get('/dashboard/continents' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []

    },
    async countryId(search?: string) {
      const res = await get('/dashboard/countries' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []

    },
    async nationalityId(search?: string) {
      const res = await get('/dashboard/countries' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []
    },
    async addressId(search?: string) {
      const res = await get('/dashboard/teams/addresses' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []
    },
    async venueId(search?: string) {
      const res = await get('/dashboard/teams/venues' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []
    },
    async coachId(search?: string) {
      const res = await get('/dashboard/teams/coaches' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []
    },
    async competitionId(search?: string) {
      const res = await get('/dashboard/competitions' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []
    },
    async teamId(search: string) {
      const res = await get('/dashboard/teams' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []
    },

  }

  const tips = {
    async bettingStrategyId(search?: string) {
      const res = await get('/dashboard/settings/picklists/betting-strategies' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []
    },
    async subscriptionDurationId(search?: string) {
      const res = await get('/dashboard/settings/picklists/subscription-duration' + prepareParams(search)).then((res) => res.data || [])
      return res.data || []
    },
    async advantagesList(search?: string) {
      const res = await get('/dashboard/settings/picklists/betting-strategies-pro-cons' + prepareParams(`${search}&type=advantage`)).then((res) => res.data || [])
      return res.data || []
    },
    async disadvantagesList(search?: string) {
      const res = await get('/dashboard/settings/picklists/betting-strategies-pro-cons' + prepareParams(`${search}&type=disadvantage`)).then((res) => res.data || [])
      return res.data || []
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