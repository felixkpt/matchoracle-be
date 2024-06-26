import { NavLink } from 'react-router-dom'
import { Icon } from '@iconify/react/dist/iconify.js'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import useAxios from '@/hooks/useAxios'
import { useEffect, useState } from 'react'
import DashJobLogsCard from '../Includes/DashJobLogsCard'
import CompetitionStatsCard from '../Includes/StatisticsJobLogsCards/Competitions'
import BettingTipsCard from '../Includes/StatisticsJobLogsCards/BettingTips'
import DashMatchJobLogsCard from './DashMatchJobLogsCard'

const Index = () => {

    const { get, loading, errors } = useAxios();
    const [stats, setStats] = useState<DashboardStatsInterface | null | undefined>(null);

    useEffect(() => {
        getStats()
    }, [])

    async function getStats() {
        get(`dashboard/automation-report`).then((response) => {
            if (response.results) {
                setStats(response.results)
            }
        })
    }

    return (
        <div className="row">
            <div className='col-12 col-xxl-10'>
                <div className="row justify-content-center mb-4">
                    <h2 className="page-title">System automation report</h2>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=seasons`} className={'link-unstyled'}>
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
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=standings-historical-results`} className={'link-unstyled'}>
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
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=standings-recent-results`} className={'link-unstyled'}>
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
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=train-predictions`} className={'link-unstyled'}>
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                        <span>Train Predictions Job</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashJobLogsCard stats={stats ? stats.train_predictions_job_logs : null} jobMessage="Preds" jobActionMessage="Preds" />
                                </div>
                            </NavLink>
                        </div>
                    </div>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=predictions`} className={'link-unstyled'}>
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

                <div className="row justify-content-center mb-4">
                    <h4>Matches Job Logs</h4>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=matches-historical-results`} className={'link-unstyled'}>
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                        <span>Historical Results</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashMatchJobLogsCard loading={loading} errors={errors} stats={stats?.matches_job_logs ? stats.matches_job_logs.historical_results : null} jobMessage="Matches" />
                                </div>
                            </NavLink>
                        </div>
                    </div>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=matches-recent-results`} className={'link-unstyled'}>
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                        <span>Recent Results</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashMatchJobLogsCard loading={loading} errors={errors} stats={stats?.matches_job_logs ? stats.matches_job_logs.recent_results : null} jobMessage="Matches" />
                                </div>
                            </NavLink>
                        </div>
                    </div>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=matches-shallow-fixtures`} className={'link-unstyled'}>
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                        <span>Shallow Fixtures</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashMatchJobLogsCard loading={loading} errors={errors} stats={stats?.matches_job_logs ? stats.matches_job_logs.shallow_fixtures : null} jobMessage="Matches" />
                                </div>
                            </NavLink>
                        </div>
                    </div>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=matches-fixtures`} className={'link-unstyled'}>
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                        <span>Fixtures</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashMatchJobLogsCard loading={loading} errors={errors} stats={stats?.matches_job_logs ? stats.matches_job_logs.fixtures : null} jobMessage="Matches" />
                                </div>
                            </NavLink>
                        </div>
                    </div>
                </div>

                <div className="row justify-content-center mb-4">
                    <h4>Match Job Logs</h4>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=match-historical-results`} className={'link-unstyled'}>
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                        <span>Historical Results</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashMatchJobLogsCard loading={loading} errors={errors} stats={stats?.match_job_logs ? stats.match_job_logs.historical_results : null} jobMessage="Match" />
                                </div>
                            </NavLink>
                        </div>
                    </div>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=match-recent-results`} className={'link-unstyled'}>
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                        <span>Recent Results</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashMatchJobLogsCard loading={loading} errors={errors} stats={stats?.match_job_logs ? stats.match_job_logs.recent_results : null} jobMessage="Match" />
                                </div>
                            </NavLink>
                        </div>
                    </div>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=match-shallow-fixtures`} className={'link-unstyled'}>
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                        <span>Shallow Fixtures</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashMatchJobLogsCard loading={loading} errors={errors} stats={stats?.match_job_logs ? stats.match_job_logs.shallow_fixtures : null} jobMessage="Match" />
                                </div>
                            </NavLink>
                        </div>
                    </div>
                    <div className="col-md-6 col-xl-4 col-xxl-4 mb-4">
                        <div className="card shadow h-100">
                            <NavLink to={`/dashboard/settings/system/job-logs?tab=match-fixtures`} className={'link-unstyled'}>
                                <div className="card-header bg-secondary text-white">
                                    <h5 className='d-flex align-items-center gap-1'>
                                        <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                        <span>Fixtures</span>
                                    </h5>
                                </div>
                                <div className="card-body text-center">
                                    <DashMatchJobLogsCard loading={loading} errors={errors} stats={stats?.match_job_logs ? stats.match_job_logs.fixtures : null} jobMessage="Match" />
                                </div>
                            </NavLink>
                        </div>
                    </div>
                </div>

                <div className='row justify-content-center mb-4'>
                    <div className="col-12">
                        <h4>Statistics Job Logs</h4>
                        <div className="row">
                            <div className="col-lg-4 mb-4">
                                <div className="card shadow h-100">
                                    <NavLink to={`/dashboard/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                                        <div className="card-header bg-secondary text-white">
                                            <h5 className='d-flex align-items-center gap-1'>
                                                <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                                <span>Competition Stats Job</span>
                                            </h5>
                                        </div>
                                        <div className="card-body text-center">
                                            <CompetitionStatsCard loading={loading} errors={errors} stats={stats?.competition_statistics_logs} />
                                        </div>
                                    </NavLink>
                                </div>
                            </div>
                            <div className="col-lg-4 mb-4">
                                <div className="card shadow h-100">
                                    <NavLink to={`/dashboard/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                                        <div className="card-header bg-secondary text-white">
                                            <h5 className='d-flex align-items-center gap-1'>
                                                <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                                <span>Compe Preds Stats Job</span>
                                            </h5>
                                        </div>
                                        <div className="card-body text-center">
                                            <CompetitionStatsCard loading={loading} errors={errors} stats={stats?.competition_prediction_statistics_logs} />
                                        </div>
                                    </NavLink>
                                </div>
                            </div>
                            <div className="col-lg-4 mb-4">
                                <div className="card shadow h-100">
                                    <NavLink to={`/dashboard/settings/system/job-logs?tab=matches`} className={'link-unstyled'}>
                                        <div className="card-header bg-secondary text-white">
                                            <h5 className='d-flex align-items-center gap-1'>
                                                <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                                <span>Betting Tips Stats Job</span>
                                            </h5>
                                        </div>
                                        <div className="card-body text-center">
                                            <BettingTipsCard loading={loading} errors={errors} stats={stats?.betting_tips_statistics_logs} />
                                        </div>
                                    </NavLink>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div className='col-12 col-xxl-2'>

                <p>
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Rem nihil, libero molestias quam culpa nam neque maxime consectetur et. Ducimus quidem tempora quam alias sit modi vel velit, odit commodi autem dolorem magni eveniet facilis quas corrupti at ipsa obcaecati? Eaque dolore ipsa ipsam provident, repudiandae dolor incidunt veritatis reiciendis ad animi qui doloribus voluptatem, veniam voluptate necessitatibus vitae fuga iusto nesciunt, assumenda sapiente facere est fugiat consectetur. Atque cumque dicta odit reprehenderit nisi cum ipsa itaque voluptatum voluptatibus accusantium fugit perspiciatis et sequi voluptate fuga laborum repellat ducimus aspernatur ratione iste minima, deserunt doloribus, nulla natus. Quam ex, asperiores aliquid, pariatur mollitia eaque deleniti fugit, minima optio vel sed nemo laborum unde aut saepe neque laboriosam sint debitis? Nesciunt sed non exercitationem vitae minus, perspiciatis ducimus recusandae corporis ipsum nemo ullam fugiat esse vero eius maxime consequatur blanditiis quidem dolore natus nihil, inventore molestiae aut! Iste enim, quidem iure inventore asperiores reprehenderit qui, autem, perferendis dolore eos voluptate vero tenetur obcaecati ratione facere illum voluptas assumenda harum. Illo, cum deleniti. Itaque, voluptate voluptatum? Placeat, nobis officia tenetur error quia expedita rerum corrupti quibusdam sed omnis minus fugit, quidem, accusantium mollitia repudiandae necessitatibus debitis! Possimus id commodi, unde sint ratione error dolorum labore explicabo itaque ex cupiditate dolores, dicta porro eius expedita! Nisi, esse vel, neque dicta commodi nobis deleniti laudantium ex repudiandae temporibus molestiae ipsum voluptatum dignissimos molestias distinctio ducimus sit laborum quo corporis tempore architecto nostrum! Alias animi placeat consectetur odio ab impedit? Perferendis, architecto sequi. Fugiat rerum aliquam laborum iste quisquam temporibus consectetur culpa corporis quos quae fuga incidunt, excepturi quo nemo eos molestiae possimus hic quaerat? Obcaecati ipsum soluta natus aut quos impedit, omnis beatae laudantium aspernatur, doloribus id fuga! Modi accusantium eveniet tempora quisquam hic doloremque commodi similique quasi. Ducimus eveniet iste, repudiandae iusto inventore provident optio esse magnam fugiat quos! Eligendi quisquam quo rem saepe numquam sint quos a voluptatibus. Incidunt quis tenetur similique rem vero voluptatibus quidem, pariatur optio. Totam ad aliquid neque excepturi iusto tempora fuga reprehenderit ex, iure ullam quasi facilis ipsa voluptatum corrupti? Placeat adipisci nostrum tempore nulla similique unde cum, non inventore. Atque accusantium molestiae nostrum quasi cum, nesciunt assumenda totam. Culpa repellendus reprehenderit non impedit voluptas aperiam vitae iste quaerat, unde temporibus magnam aspernatur omnis? Dolor delectus repellendus nulla voluptas totam corporis, dolore nesciunt sed deserunt dolorem eligendi harum nostrum labore. Quos expedita totam veniam magni ad placeat?
                </p>
            </div>
        </div>
    )
}

export default Index