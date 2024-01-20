import { NavLink } from 'react-router-dom'
import { Icon } from '@iconify/react/dist/iconify.js'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import useAxios from '@/hooks/useAxios'
import { useEffect, useState } from 'react'
import DashJobLogsCard from '../Includes/DashJobLogsCard'
import Loader from '@/components/Loader'
import CompetitionStatsCard from '../Includes/StatisticsJobLogsCards/Competitions'
import BettingTipsCard from '../Includes/StatisticsJobLogsCards/BettingTips'
import DashMatchJobLogsCard from './DashMatchJobLogsCard'

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
        <div>
            <div className="row mb-4">
                <h2 className="page-title">System automation report</h2>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=seasons`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'material-symbols:event-upcoming-sharp'}`} />
                                    <span>Seasons Job</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashJobLogsCard stats={stats ? stats.seasons_job_logs : null} jobMessage="Seasons" />
                            </div>
                        </NavLink>
                    </div>
                </div>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=standings-historical-results`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'gg:list'}`} />
                                    <span>Standings Job - Historical</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashJobLogsCard stats={stats?.standings_job_logs ? stats.standings_job_logs.recent_results : null} jobMessage="Standings" />
                            </div>
                        </NavLink>
                    </div>
                </div>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=standings-recent-results`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'gg:list'}`} />
                                    <span>Standings Job - Recent</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashJobLogsCard stats={stats?.standings_job_logs ? stats.standings_job_logs.recent_results : null} jobMessage="Standings" />
                            </div>
                        </NavLink>
                    </div>
                </div>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=predictions`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                    <span>Predictions Job</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashJobLogsCard stats={stats ? stats.predictions_job_logs : null} jobMessage="Preds" jobActionMessage="Preds" />
                            </div>
                        </NavLink>
                    </div>
                </div>

            </div>

            <div className="row mb-4">
                <h4>Matches Job Logs</h4>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=matches-historical-results`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Historical Results</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashMatchJobLogsCard stats={stats?.matches_job_logs ? stats.matches_job_logs.historical_results : null} jobMessage="Matches" />
                            </div>
                        </NavLink>
                    </div>
                </div>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=matches-recent-results`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Recent Results</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashMatchJobLogsCard stats={stats?.matches_job_logs ? stats.matches_job_logs.recent_results : null} jobMessage="Matches" />
                            </div>
                        </NavLink>
                    </div>
                </div>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=matches-shallow-fixtures`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Shallow Fixtures</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashMatchJobLogsCard stats={stats?.matches_job_logs ? stats.matches_job_logs.shallow_fixtures : null} jobMessage="Matches" />
                            </div>
                        </NavLink>
                    </div>
                </div>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=matches-fixtures`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Fixtures</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashMatchJobLogsCard stats={stats?.matches_job_logs ? stats.matches_job_logs.fixtures : null} jobMessage="Matches" />
                            </div>
                        </NavLink>
                    </div>
                </div>
            </div>

            <div className="row mb-4">
                <h4>Match Job Logs</h4>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=match-historical-results`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Historical Results</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashMatchJobLogsCard stats={stats?.match_job_logs ? stats.match_job_logs.historical_results : null} jobMessage="Match" />
                            </div>
                        </NavLink>
                    </div>
                </div>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=match-recent-results`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Recent Results</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashMatchJobLogsCard stats={stats?.match_job_logs ? stats.match_job_logs.recent_results : null} jobMessage="Match" />
                            </div>
                        </NavLink>
                    </div>
                </div>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=match-shallow-fixtures`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Shallow Fixtures</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashMatchJobLogsCard stats={stats?.match_job_logs ? stats.match_job_logs.shallow_fixtures : null} jobMessage="Match" />
                            </div>
                        </NavLink>
                    </div>
                </div>
                <div className="col-md-6 col-xl-4 col-xxl-3 mb-4">
                    <div className="card shadow h-100">
                        <NavLink to={`/admin/settings/system/job-logs?tab=match-fixtures`} className={'link-unstyled'}>
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Fixtures</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                <DashMatchJobLogsCard stats={stats?.match_job_logs ? stats.match_job_logs.fixtures : null} jobMessage="Match" />
                            </div>
                        </NavLink>
                    </div>
                </div>
            </div>

            <div className='row mb-4'>
                <div className="col-12">
                    <h4>Statistics Job Logs</h4>
                    <div className="row">
                        <div className="col-lg-4 mb-4">
                            <div className="card shadow h-100">
                                <NavLink to={`/admin/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
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
                                </NavLink>
                            </div>
                        </div>
                        <div className="col-lg-4 mb-4">
                            <div className="card shadow h-100">
                                <NavLink to={`/admin/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
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
                                </NavLink>
                            </div>
                        </div>
                        <div className="col-lg-4 mb-4">
                            <div className="card shadow h-100">
                                <NavLink to={`/admin/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                                    <div className="card-header bg-secondary text-white">
                                        <h5 className='d-flex align-items-center gap-1'>
                                            <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                            <span>Betting Tips Stats Job</span>
                                        </h5>
                                    </div>
                                    <div className="card-body text-center">
                                        {
                                            stats?.betting_tips_statistics_logs ?
                                                <BettingTipsCard stats={stats.betting_tips_statistics_logs} />
                                                :
                                                <Loader />
                                        }
                                    </div>
                                </NavLink>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default Index