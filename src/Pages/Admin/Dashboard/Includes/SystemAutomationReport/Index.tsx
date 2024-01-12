import DashJobLogsCard from '../DashJobLogsCard'
import { NavLink } from 'react-router-dom'
import { Icon } from '@iconify/react/dist/iconify.js'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import useAxios from '@/hooks/useAxios'
import { useEffect, useState } from 'react'

type Props = {
}

const Index = ({ }: Props) => {
    const { get, loading, errors } = useAxios();
    const [stats, setStats] = useState<DashboardStatsInterface | null>(null);

    useEffect(() => {
        getStats()
    }, [])

    async function getStats() {
        get(`admin/automation-report`).then((results: any) => {
            if (results) {
                setStats(results)
            }
        })
    }

    return (
        <div className="row mb-4">
            <h5>System automation report</h5>
            <div className="col-md-6 col-xl-4 mb-4">
                <NavLink to={`/admin/settings/system/job-logs?tab=seasons`} className={'link-unstyled'}>
                    <div className="card shadow">
                        <div className="card-header bg-secondary text-white">
                            <h5 className='d-flex align-items-center gap-1'>
                                <Icon width={'2rem'} icon={`${'material-symbols:event-upcoming-sharp'}`} />
                                <span>Seasons Job</span>
                            </h5>
                        </div>
                        <div className="card-body text-center">
                            <DashJobLogsCard stats={stats ? stats.seasons_job_logs : null} jobMessage="Seasons" />
                        </div>
                    </div>
                </NavLink>
            </div>
            <div className="col-md-6 col-xl-4 mb-4">
                <NavLink to={`/admin/settings/system/job-logs?tab=standings`} className={'link-unstyled'}>
                    <div className="card shadow">
                        <div className="card-header bg-secondary text-white">
                            <h5 className='d-flex align-items-center gap-1'>
                                <Icon width={'2rem'} icon={`${'gg:list'}`} />
                                <span>Standings Job</span>
                            </h5>
                        </div>
                        <div className="card-body text-center">
                            <DashJobLogsCard stats={stats ? stats.standings_job_logs : null} jobMessage="Standings" />
                        </div>
                    </div>
                </NavLink>
            </div>
            <div className="col-md-6 col-xl-4 mb-4">
                <NavLink to={`/admin/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                    <div className="card shadow">
                        <div className="card-header bg-secondary text-white">
                            <h5 className='d-flex align-items-center gap-1'>
                                <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                <span>Matches Job - Recent Results</span>
                            </h5>
                        </div>
                        <div className="card-body text-center">
                            <DashJobLogsCard stats={stats ? stats.results_match_job_logs : null} jobMessage="Matches" />
                        </div>
                    </div>
                </NavLink>
            </div>
            <div className="col-md-6 col-xl-4 mb-4">
                <NavLink to={`/admin/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                    <div className="card shadow">
                        <div className="card-header bg-secondary text-white">
                            <h5 className='d-flex align-items-center gap-1'>
                                <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                <span>Matches Job - Shallow Fixtures</span>
                            </h5>
                        </div>
                        <div className="card-body text-center">
                            <DashJobLogsCard stats={stats ? stats.shallow_fixtures_matches_job_logs : null} jobMessage="Matches" />
                        </div>
                    </div>
                </NavLink>
            </div>
            <div className="col-md-6 col-xl-4 mb-4">
                <NavLink to={`/admin/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                    <div className="card shadow">
                        <div className="card-header bg-secondary text-white">
                            <h5 className='d-flex align-items-center gap-1'>
                                <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                <span>Matches Job - Fixtures</span>
                            </h5>
                        </div>
                        <div className="card-body text-center">
                            <DashJobLogsCard stats={stats ? stats.fixtures_matches_job_logs : null} jobMessage="Matches" />
                        </div>
                    </div>
                </NavLink>
            </div>
            <div className="col-md-6 col-xl-4 mb-4">
                <NavLink to={`/admin/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                    <div className="card shadow">
                        <div className="card-header bg-secondary text-white">
                            <h5 className='d-flex align-items-center gap-1'>
                                <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                <span>Matches Job - Historical Results</span>
                            </h5>
                        </div>
                        <div className="card-body text-center">
                            <DashJobLogsCard stats={stats ? stats.historical_results_matches_job_logs : null} jobMessage="Matches" />
                        </div>
                    </div>
                </NavLink>
            </div>
            <div className="col-md-6 col-xl-4 mb-4">
                <NavLink to={`/admin/settings/system/job-logs?tab=results-match`} className={'link-unstyled'}>
                    <div className="card shadow">
                        <div className="card-header bg-secondary text-white">
                            <h5 className='d-flex align-items-center gap-1'>
                                <Icon width={'2rem'} icon={`${'mdi:football-helmet'}`} />
                                <span>Match Job - Recent Results</span>
                            </h5>
                        </div>
                        <div className="card-body text-center">
                            <DashJobLogsCard stats={stats ? stats.results_match_job_logs : null} jobMessage="Match" />
                        </div>
                    </div>
                </NavLink>
            </div>
            <div className="col-md-6 col-xl-4 mb-4">
                <NavLink to={`/admin/settings/system/job-logs?tab=fixtures-match`} className={'link-unstyled'}>
                    <div className="card shadow">
                        <div className="card-header bg-secondary text-white">
                            <h5 className='d-flex align-items-center gap-1'>
                                <Icon width={'2rem'} icon={`${'mdi:football-helmet'}`} />
                                <span>Match Job - Fixtures</span>
                            </h5>
                        </div>
                        <div className="card-body text-center">
                            <DashJobLogsCard stats={stats ? stats.fixtures_match_job_logs : null} jobMessage="Match" />
                        </div>
                    </div>
                </NavLink>
            </div>
            <div className="col-md-6 col-xl-4 mb-4">
                <NavLink to={`/admin/settings/system/job-logs?tab=historical-results-match`} className={'link-unstyled'}>
                    <div className="card shadow">
                        <div className="card-header bg-secondary text-white">
                            <h5 className='d-flex align-items-center gap-1'>
                                <Icon width={'2rem'} icon={`${'mdi:football-helmet'}`} />
                                <span>Match Job - Historical Results</span>
                            </h5>
                        </div>
                        <div className="card-body text-center">
                            <DashJobLogsCard stats={stats ? stats.historical_results_match_job_logs : null} jobMessage="Match" />
                        </div>
                    </div>
                </NavLink>
            </div>
        </div>
    )
}

export default Index