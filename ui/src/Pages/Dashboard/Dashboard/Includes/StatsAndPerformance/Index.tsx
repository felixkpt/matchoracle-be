import { Icon } from '@iconify/react/dist/iconify.js'
import { NavLink } from 'react-router-dom'
import DetailedMatchesInfo from '../DetailedMatchesInfo'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import useAxios from '@/hooks/useAxios'
import { useEffect, useState } from 'react'
import NoContentMessage from '@/components/NoContentMessage'


const Index = () => {

    const { get, loading, errors } = useAxios();
    const [stats, setStats] = useState<DashboardStatsInterface | null>(null);

    useEffect(() => {
        getStats()
    }, [])

    async function getStats() {
        get(`dashboard/advanced-stats`).then((response) => {
            if (response.results) {
                setStats(response.results)
            }
        })
    }

    return (
        <div className="row mb-4 align-items-start">
            <h5>Statistics & Performace</h5>
            <div className="col-xxl-8">
                <div className='row'>
                    <div className="col-12">
                        <div className="row">
                            <div className="col-lg-6 mb-4">
                                <NavLink to={`/dashboard/matches`} className={'link-unstyled'}>
                                    <div className="card card-primary">
                                        <div className="card-header">
                                            <h5 className='d-flex align-items-center gap-1'>
                                                <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                                <span>Matches detailed info</span>
                                            </h5>
                                        </div>
                                        <div className="card-body text-center">
                                            <DetailedMatchesInfo
                                                loading={loading}
                                                errors={errors}
                                                stats={stats?.advanced_matches}
                                            />
                                        </div>
                                    </div>
                                </NavLink>
                            </div>
                            <div className="col-lg-6 mb-4">
                                <NavLink to={`/dashboard/settings/system/predictions-performance`} className={'link-unstyled'}>
                                    <div className="card card-primary">
                                        <div className="card-header">
                                            <h5 className='d-flex align-items-center gap-1'>
                                                <Icon width={'2rem'} icon={`${'mdi:chart-line-variant'}`} />
                                                <span>Predictions performace overview</span>
                                            </h5>
                                        </div>
                                        <div className="card-body text-center">
                                            <NoContentMessage />
                                        </div>
                                    </div>
                                </NavLink>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div className="col-xxl-4">
               Lorem ipsum dolor sit, amet consectetur adipisicing elit. Itaque iste, dolores et cum officia tempore blanditiis commodi maxime quis at quasi quaerat, nihil doloremque neque necessitatibus dolor nesciunt atque voluptatum?
            </div>
        </div>
    )
}

export default Index