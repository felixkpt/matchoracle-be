import { Icon } from '@iconify/react/dist/iconify.js'
import { NavLink } from 'react-router-dom'
import DashMiniCard from './DashMiniCard'
import DashPastUpcomingCard from './DashPastUpcomingCard'
import Loader from '@/components/Loader'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'
import useAxios from '@/hooks/useAxios'

const TopMainStats = () => {

    const { get, loading, errors } = useAxios();

    const [stats, setStats] = useState<DashboardStatsInterface | null>(null);

    useEffect(() => {
      getStats()
    }, [])
  
    async function getStats() {
      get(`dashboard/stats`).then((results: any) => {
        if (results) {
          setStats(results)
        }
      })
    }
  
    const countries = stats?.countries
    const competitions = stats?.competitions
    const odds_enabled_competitions = stats?.odds_enabled_competitions
    const seasons = stats?.seasons
    const standings = stats?.seasons
    const teams = stats?.seasons
    const matches = stats?.matches
    const predictions = stats?.predictions
    const odds = stats?.odds

    return (
        <div>
            <div className="row justify-content-center">
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/dashboard/countries`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'gis:search-country'}`} />
                                    <span>Countries</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    countries && countries.totals >= 0
                                        ?
                                        <>
                                            <div className='mb-3'>
                                                <span className="shadow-sm p-2 rounded text-muted fs-5">Total: {countries.totals}</span>
                                            </div>

                                            <div className="row align-items-center justify-content-between">
                                                <div className='col-sm-12 shadow-sm rounded text-success'>
                                                    <div className="d-flex justify-content-between align-items-center gap-2">
                                                        <span className='d-flex align-items-center gap-1'>
                                                            <Icon width={'1rem'} icon={`${'ic:sharp-published-with-changes'}`} />
                                                            With compe:
                                                        </span>
                                                        <span>{countries.with_competitions}</span>
                                                    </div>
                                                </div>
                                                <div className='col-sm-12 shadow-sm rounded text-danger'>
                                                    <div className="d-flex justify-content-between align-items-center gap-2">
                                                        <span className='d-flex align-items-center gap-1'>
                                                            <Icon width={'1rem'} icon={`${'fe:disabled'}`} />
                                                            No compe:
                                                        </span>
                                                        <span>{countries.without_competitions}</span>
                                                    </div>
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
                    <NavLink to={`/dashboard/competitions`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'twemoji:trophy'}`} />
                                    <span>Competitions</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    competitions && competitions.totals >= 0
                                        ?
                                        <DashMiniCard total={stats ? competitions.totals : 0} active={stats ? competitions.active : 0} inactive={stats ? competitions.inactive : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/dashboard/competitions?tab=oddsenabled`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'bx:bxs-trophy'}`} />
                                    <span>Odds Enabled Competitions</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    odds_enabled_competitions && odds_enabled_competitions.totals >= 0
                                        ?
                                        <DashMiniCard total={stats ? odds_enabled_competitions.totals : 0} active={stats ? odds_enabled_competitions.active : 0} inactive={stats ? odds_enabled_competitions.inactive : 0} />
                                        :
                                        <Loader />
                                }

                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/dashboard/teams`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'material-symbols:event-upcoming-sharp'}`} />
                                    <span>Seasons</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    seasons && seasons.totals >= 0
                                        ?
                                        <DashMiniCard total={stats ? seasons.totals : 0} active={stats ? seasons.active : 0} inactive={stats ? seasons.inactive : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/dashboard/teams`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'gg:list'}`} />
                                    <span>Standings</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    standings && standings.totals >= 0
                                        ?
                                        <DashMiniCard total={stats ? standings.totals : 0} active={stats ? standings.active : 0} inactive={stats ? standings.inactive : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/dashboard/teams`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'fluent:group-24-filled'}`} />
                                    <span>Teams</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    teams && teams.totals >= 0
                                        ?
                                        <DashMiniCard total={stats ? teams.totals : 0} active={stats ? teams.active : 0} inactive={stats ? teams.inactive : 0} />
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
                    <NavLink to={`/dashboard/matches`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'game-icons:soccer-kick'}`} />
                                    <span>Matches</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    matches && matches.totals >= 0
                                        ?
                                        <DashPastUpcomingCard total={stats ? matches.totals : 0} past={stats ? matches.past : 0} upcoming={stats ? stats.matches.upcoming : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/dashboard/predictions`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                    <span>Predictions</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    predictions && predictions.totals >= 0
                                        ?
                                        <DashPastUpcomingCard total={stats ? predictions.totals : 0} past={stats ? predictions.past : 0} upcoming={stats ? stats.predictions.upcoming : 0} />
                                        :
                                        <Loader />
                                }
                            </div>
                        </div>
                    </NavLink>
                </div>
                <div className="col-lg-6 col-xl-4 mb-4">
                    <NavLink to={`/dashboard/odds`} className={'link-unstyled'}>
                        <div className="card shadow">
                            <div className="card-header bg-secondary text-white">
                                <h5 className='d-flex align-items-center gap-1'>
                                    <Icon width={'2rem'} icon={`${'mdi:soccer-field'}`} />
                                    <span>Odds</span>
                                </h5>
                            </div>
                            <div className="card-body text-center">
                                {
                                    odds && odds.totals >= 0
                                        ?
                                        <DashPastUpcomingCard total={stats ? odds.totals : 0} past={stats ? odds.past : 0} upcoming={stats ? odds.upcoming : 0} />
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