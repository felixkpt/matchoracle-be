import { Icon } from '@iconify/react/dist/iconify.js'
import { NavLink } from 'react-router-dom'
import DashMiniCard from './DashMiniCard'
import DashPastUpcomingCard from './DashPastUpcomingCard'
import Loader from '@/components/Loader'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'

type Props = {
    stats: DashboardStatsInterface | null
}

const TopMainStats = ({ stats }: Props) => {
    return (
        <div>
            <div className="row justify-content-center">
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/admin/countries`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'gis:search-country'}`} />
                                    <span>Countries</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    stats?.countries.totals
                                        ?
                                        <>
                                            <div className='mb-3'>
                                                <span className="shadow-sm p-2 rounded text-muted fs-5">Total: {stats.countries.totals}</span>
                                            </div>
                                            <div className="d-flex align-items-center gap-1 justify-content-between">
                                                <div className='d-flex align-items-center gap-2 shadow-sm p-2 rounded text-success'>
                                                    <span className='d-flex align-items-center gap-1'>
                                                        <Icon width={'1rem'} icon={`${'ic:sharp-published-with-changes'}`} />
                                                        With compe:
                                                    </span>
                                                    {stats?.countries.with_competitions}
                                                </div>
                                                <div className='d-flex align-items-center gap-2 shadow-sm p-2 rounded text-info'>
                                                    <span className='d-flex align-items-center gap-1'>
                                                        <Icon width={'1rem'} icon={`${'fe:disabled'}`} />
                                                        No compe:
                                                    </span>
                                                    {stats?.countries.without_competitions}
                                                </div>
                                            </div>
                                        </>
                                        :
                                        <Loader />
                                }

                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/admin/competitions`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'twemoji:trophy'}`} />
                                    <span>Competitions</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    stats?.competitions.totals
                                        ?
                                        <DashMiniCard total={stats ? stats.competitions.totals : 0} active={stats ? stats.competitions.active : 0} inactive={stats ? stats.competitions.inactive : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/admin/competitions?tab=oddsenabled`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'bx:bxs-trophy'}`} />
                                    <span>Odds Enabled Competitions</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    stats?.odds_enabled_competitions.totals
                                        ?
                                        <DashMiniCard total={stats ? stats.odds_enabled_competitions.totals : 0} active={stats ? stats.odds_enabled_competitions.active : 0} inactive={stats ? stats.odds_enabled_competitions.inactive : 0} />
                                        :
                                        <Loader />
                                }

                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/admin/teams`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'material-symbols:event-upcoming-sharp'}`} />
                                    <span>Seasons</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    stats?.seasons.totals
                                        ?
                                        <DashMiniCard total={stats ? stats.seasons.totals : 0} active={stats ? stats.seasons.active : 0} inactive={stats ? stats.seasons.inactive : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/admin/teams`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'gg:list'}`} />
                                    <span>Standings</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    stats?.standings.totals
                                        ?
                                        <DashMiniCard total={stats ? stats.standings.totals : 0} active={stats ? stats.standings.active : 0} inactive={stats ? stats.standings.inactive : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/admin/teams`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'fluent:group-24-filled'}`} />
                                    <span>Teams</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    stats?.teams.totals
                                        ?
                                        <DashMiniCard total={stats ? stats.teams.totals : 0} active={stats ? stats.teams.active : 0} inactive={stats ? stats.teams.inactive : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
            </div>

            <div className="row justify-content-center">
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/admin/matches`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Matches</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    stats?.matches
                                        ?
                                        <DashPastUpcomingCard total={stats ? stats.matches.totals : 0} past={stats ? stats.matches.past : 0} upcoming={stats ? stats.matches.upcoming : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/admin/predictions`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                    <span>Predictions</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    stats?.predictions.totals
                                        ?
                                        <DashPastUpcomingCard total={stats ? stats.predictions.totals : 0} past={stats ? stats.predictions.past : 0} upcoming={stats ? stats.predictions.upcoming : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/admin/odds`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                    <span>Odds</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    stats?.matches
                                        ?
                                        <DashPastUpcomingCard total={stats ? stats.odds.totals : 0} past={stats ? stats.odds.past : 0} upcoming={stats ? stats.odds.upcoming : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
            </div>
        </div>
    )
}

export default TopMainStats