import { NavLink } from 'react-router-dom'
import { Icon } from '@iconify/react/dist/iconify.js'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import useAxios from '@/hooks/useAxios'
import { useEffect, useState } from 'react'
import DashJobLogsCard from './Includes/DashJobLogsCard'
import Loader from '@/components/Loader'
import CompetitionStatsCard from '@/Pages/Admin/Dashboard/Includes/CompetitionStatisticsStatsCard/Index'

type Props = {
}

const AutomationReport = ({ }: Props) => {

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
            <h2 className="page-title">System automation report</h2>
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
            <div className="col-12">
                <div className="row">
                    <div className="col-lg-6 mb-4">
                        <NavLink to={`/admin/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                            <div className="card shadow">
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                        <span>Competition Stats Job</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    {
                                        stats?.competition_statistics_logs ?
                                            <CompetitionStatsCard stats={stats.competition_statistics_logs} />
                                            :
                                            <Loader />
                                    }
                                </div>
                            </div>
                        </NavLink>
                    </div>
                    <div className="col-lg-6 mb-4">
                        <NavLink to={`/admin/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                            <div className="card shadow">
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                        <span>Competition Prediction Stats Job</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    {
                                        stats?.competition_prediction_statistics_logs ?
                                            <CompetitionStatsCard stats={stats.competition_prediction_statistics_logs} />
                                            :
                                            <Loader />
                                    }
                                </div>
                            </div>
                        </NavLink>
                    </div>
                    <div className="col-lg-6 mb-4">
                        <NavLink to={`/admin/settings/system/job-logs?tab=predictions`} className={'link-unstyled'}>
                            <div className="card shadow">
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                        <span>Predictions Job</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashJobLogsCard stats={stats ? stats.predictions_job_logs : null} jobMessage="Predictions" jobActionMessage="Prediction" />
                                </div>
                            </div>
                        </NavLink>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default AutomationReport