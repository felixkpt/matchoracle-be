import AutoTabs from '@/components/Autos/AutoTabs'
import { TabInterface } from '@/interfaces/UncategorizedInterfaces'
import Profile from './Tabs/Profile'
import LoginLogs from './Tabs/LoginLogs'
import Roles from './Tabs/Roles'

const Index = () => {

    const tabs: TabInterface[] = [
        {
            name: 'Profile',
            component: <Profile />
        },
        {
            name: 'Roles',
            component: <Roles />
        },{
            name: 'Login Logs',
            component: <LoginLogs />
        },
    ]

    return (
        <div>
            <AutoTabs tabs={tabs} title='Account' />
        </div>
    )
}

export default Index