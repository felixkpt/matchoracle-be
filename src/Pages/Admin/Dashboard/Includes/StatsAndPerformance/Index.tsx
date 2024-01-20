import { Icon } from '@iconify/react/dist/iconify.js'
import { NavLink } from 'react-router-dom'
import DetailedMatchesInfo from '../DetailedMatchesInfo'
import Loader from '@/components/Loader'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import useAxios from '@/hooks/useAxios'
import { useEffect, useState } from 'react'
import BettingTipsStats from './BettingTipsStats'

type Props = {
}

const Index = ({ }: Props) => {

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

    console.log(stats)

    return (
        <div className="row mb-4 align-items-start">
            <h5>Statistics & Performace</h5>
            <div className="col-xxl-8">
                <div className='row'>
                    <div className="col-12">
                        <div className="row">
                            <div className="col-lg-6 mb-4">
                                <NavLink to={`/admin/matches`} className={'link-unstyled'}>
                                    <div className="card shadow">
                                        <div className="card-header bg-secondary text-white">
                                            <h5 className='d-flex align-items-center gap-1'>
                                                <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                                <span>Matches detailed info</span>
                                            </h5>
                                        </div>
                                        <div className="card-body text-center">
                                            {
                                                stats?.advanced_matches ?
                                                    <DetailedMatchesInfo stats={stats.advanced_matches} />
                                                    :
                                                    <Loader />
                                            }
                                        </div>
                                    </div>
                                </NavLink>
                            </div>
                            <div className="col-lg-6 mb-4">
                                <NavLink to={`/admin/settings/system/predictions-performance`} className={'link-unstyled'}>
                                    <div className="card shadow">
                                        <div className="card-header bg-secondary text-white">
                                            <h5 className='d-flex align-items-center gap-1'>
                                                <Icon width={'2rem'} icon={`${'mdi:chart-line-variant'}`} />
                                                <span>Predictions performace overview</span>
                                            </h5>
                                        </div>
                                        <div className="card-body text-center">
                                        </div>
                                    </div>
                                </NavLink>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div className="col-xxl-4">
                {
                    stats?.betting_tips_statistics ?
                        <BettingTipsStats stats={stats.betting_tips_statistics} />
                        :
                        <Loader />
                }

            </div>

        </div>
    )
}

export default Index