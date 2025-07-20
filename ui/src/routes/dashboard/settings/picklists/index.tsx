
import gamesources from '@/routes/dashboard/settings/picklists/game-sources'
import statuses from '@/routes/dashboard/settings/picklists/statuses'

const index = [

    {
        path: 'statuses',
        children: statuses,
    },
    {
        path: 'game-sources',
        children: gamesources,
    }
]

export default index