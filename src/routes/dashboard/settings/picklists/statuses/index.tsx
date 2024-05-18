
import defaultStatuses from '@/routes/dashboard/settings/picklists/statuses/default'
import gameScoreStatuses from '@/routes/dashboard/settings/picklists/statuses/game-score-statuses'

const index = [

    {
        path: 'default',
        children: defaultStatuses,
    },
    {
        path: 'game-score-statuses',
        children: gameScoreStatuses,
    },
]

export default index