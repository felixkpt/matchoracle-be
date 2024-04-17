
import defaultStatuses from '@/routes/admin/settings/picklists/statuses/default'
import post from '@/routes/admin/settings/picklists/statuses/post'
import gameScoreStatuses from '@/routes/admin/settings/picklists/statuses/game-score-statuses'

const index = [

    {
        path: 'default',
        children: defaultStatuses,
    },
    {
        path: 'post',
        children: post,
    },
    {
        path: 'game-score-statuses',
        children: gameScoreStatuses,
    },
]

export default index