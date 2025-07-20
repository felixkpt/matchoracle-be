import AutoTabs from '@/components/Autos/AutoTabs'
import { TabInterface } from '@/interfaces/UncategorizedInterfaces'
import Profile from './Tabs/Profile'
import LoginLogs from './Tabs/LoginLogs'
import Roles from './Tabs/Roles'
import { useAuth } from '@/contexts/AuthContext'
import { useLocation, useNavigate } from 'react-router-dom'

const Index = () => {

    const { user, setRedirectTo } = useAuth()
    const navigate = useNavigate()
    const location = useLocation();

    if (!user) {
        setRedirectTo(location.pathname + location.search + location.hash)
        return navigate('/login')
    }

    const tabs: TabInterface[] = [
        {
            name: 'Profile',
            component: <Profile />
        },
        {
            name: 'Roles',
            component: <Roles />
        },
        {
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