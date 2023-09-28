
import defaultStatuses from '@/routes/admin/settings/picklists/statuses/default'
import post from '@/routes/admin/settings/picklists/statuses/post'

const index = [

    {
        path: 'default',
        children: defaultStatuses,
    },
    {
        path: 'post',
        children: post,
    },
]

export default index