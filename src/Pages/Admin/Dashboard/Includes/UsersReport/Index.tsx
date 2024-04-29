import useAxios from '@/hooks/useAxios';
import UsersMiniCardSection from './UsersMiniCardSection'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react';

type Props = {
}

const Index = ({  }: Props) => {

    const { get, loading, errors } = useAxios();
    const [stats, setStats] = useState<DashboardStatsInterface | null>(null);

    useEffect(() => {
        getStats()
    }, [])

    async function getStats() {
        get(`admin/advanced-stats`).then((results: any) => {
            if (results) {
                setStats(results)
            }
        })
    }

    return (
        <div className="row mb-4">
            <h5>Users report</h5>
            <UsersMiniCardSection
                to={`/admin/countries`}
                icon={'bx:bxs-user'}
                title={'Users'}
                total={stats ? stats.users.totals : 0}
                active={stats ? stats.users.active : 0}
                inactive={stats ? stats.users.inactive : 0}
            />
            <UsersMiniCardSection
                to={`/admin/competitions`}
                icon={'fluent:people-team-16-filled'}
                title={'Subscribed Users'}
                total={stats ? stats.subscribed_users.totals : 0}
                active={stats ? stats.subscribed_users.active : 0}
                inactive={stats ? stats.subscribed_users.inactive : 0}
            />
            <UsersMiniCardSection
                to={`/admin/tipsters`}
                icon={'game-icons:team-idea'}
                title={'Tipsters'}
                total={stats ? stats.tipsters.totals : 0}
                active={stats ? stats.tipsters.active : 0}
                inactive={stats ? stats.tipsters.inactive : 0}
            />
        </div>
    )
}

export default Index