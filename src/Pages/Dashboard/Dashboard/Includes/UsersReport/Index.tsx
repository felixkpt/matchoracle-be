import useAxios from '@/hooks/useAxios';
import UsersMiniCardSection from './UsersMiniCardSection'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react';

const Index = () => {

    const { get, loading, errors } = useAxios();
    const [stats, setStats] = useState<DashboardStatsInterface | null>(null);

    useEffect(() => {
        getStats()
    }, [])

    async function getStats() {
        get(`dashboard/advanced-stats`).then((results) => {
            if (results.data) {
                setStats(results.data)
            }
        })
    }

    return (
        <div className="row mb-4">
            <h5>Users report</h5>
            <UsersMiniCardSection
                loading={loading}
                errors={errors}
                to={`/dashboard/countries`}
                icon={'bx:bxs-user'}
                title={'Users'}
                total={stats ? stats.users?.totals : 0}
                active={stats ? stats.users?.active : 0}
                inactive={stats ? stats.users?.inactive : 0}
            />
            <UsersMiniCardSection
                loading={loading}
                errors={errors}
                to={`/dashboard/competitions`}
                icon={'fluent:people-team-16-filled'}
                title={'Subscribed Users'}
                total={stats ? stats.subscribed_users?.totals : 0}
                active={stats ? stats.subscribed_users?.active : 0}
                inactive={stats ? stats.subscribed_users?.inactive : 0}
            />
            <UsersMiniCardSection
                loading={loading}
                errors={errors}
                to={`/dashboard/tipsters`}
                icon={'game-icons:team-idea'}
                title={'Tipsters'}
                total={stats ? stats.tipsters?.totals : 0}
                active={stats ? stats.tipsters?.active : 0}
                inactive={stats ? stats.tipsters?.inactive : 0}
            />
        </div>
    )
}

export default Index