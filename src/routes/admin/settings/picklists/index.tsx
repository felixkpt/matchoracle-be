
import gamesources from '@/routes/admin/settings/picklists/game-sources'
import statuses from '@/routes/admin/settings/picklists/statuses'

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