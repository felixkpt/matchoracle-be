import { Icon } from '@iconify/react/dist/iconify.js'
import DashMiniCard from './DashMiniCard'
import DashPastUpcomingCard from './DashPastUpcomingCard'
import Loader from '@/components/Loader'
import { DashboardStatsInterface } from '@/interfaces/FootballInterface'
import { useEffect, useState } from 'react'
import useAxios from '@/hooks/useAxios'
import TopMainStatsCard from './TopMainStatsCard'
import NoContentMessage from '@/components/NoContentMessage'

const TopMainStats = () => {

    const { get, loading } = useAxios();

    const [stats, setStats] = useState<DashboardStatsInterface | null>(null);

    useEffect(() => {
        getStats()
    }, [])

    async function getStats() {
        get(`dashboard/stats`).then((results) => {
            if (results.data) {
                setStats(results.data)
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
                <TopMainStatsCard title='Countries' link='/dashboard/countries' icon='gis:search-country'>
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
                            <>
                                {
                                    loading ?
                                        <Loader />
                                        :
                                        <NoContentMessage message={`${!stats ? '' : 'Data error'}`} />
                                }
                            </>
                    }
                </TopMainStatsCard>
                <TopMainStatsCard title='Competitions' link='/dashboard/competitions' icon='twemoji:trophy'>
                    {
                        competitions && competitions.totals >= 0
                            ?
                            <DashMiniCard total={stats ? competitions.totals : 0} active={stats ? competitions.active : 0} inactive={stats ? competitions.inactive : 0} />
                            :
                            <>
                                {
                                    loading ?
                                        <Loader />
                                        :
                                        <NoContentMessage message={`${!stats ? '' : 'Data error'}`} />
                                }
                            </>
                    }
                </TopMainStatsCard>
                <TopMainStatsCard title='Odds Enabled Competitions' link='/dashboard/competitions?tab=oddsenabled' icon='bx:bxs-trophy'>
                    {
                        odds_enabled_competitions && odds_enabled_competitions.totals >= 0
                            ?
                            <DashMiniCard total={stats ? odds_enabled_competitions.totals : 0} active={stats ? odds_enabled_competitions.active : 0} inactive={stats ? odds_enabled_competitions.inactive : 0} />
                            :
                            <>
                                {
                                    loading ?
                                        <Loader />
                                        :
                                        <NoContentMessage message={`${!stats ? '' : 'Data error'}`} />
                                }
                            </>
                    }
                </TopMainStatsCard>
                <TopMainStatsCard title='Seasons' link='/dashboard/seasons' icon='material-symbols:event-upcoming-sharp'>
                    {
                        seasons && seasons.totals >= 0
                            ?
                            <DashMiniCard total={stats ? seasons.totals : 0} active={stats ? seasons.active : 0} inactive={stats ? seasons.inactive : 0} />
                            :
                            <>
                                {
                                    loading ?
                                        <Loader />
                                        :
                                        <NoContentMessage message={`${!stats ? '' : 'Data error'}`} />
                                }
                            </>
                    }
                </TopMainStatsCard>
                <TopMainStatsCard title='Standings' link='/dashboard/teams' icon='gg:list'>
                    {
                        standings && standings.totals >= 0
                            ?
                            <DashMiniCard total={stats ? standings.totals : 0} active={stats ? standings.active : 0} inactive={stats ? standings.inactive : 0} />
                            :
                            <>
                                {
                                    loading ?
                                        <Loader />
                                        :
                                        <NoContentMessage message={`${!stats ? '' : 'Data error'}`} />
                                }
                            </>
                    }
                </TopMainStatsCard>
                <TopMainStatsCard title='Teams' link='/dashboard/teams' icon='fluent:group-24-filled'>
                    {
                        teams && teams.totals >= 0
                            ?
                            <DashMiniCard total={stats ? teams.totals : 0} active={stats ? teams.active : 0} inactive={stats ? teams.inactive : 0} />
                            :
                            <>
                                {
                                    loading ?
                                        <Loader />
                                        :
                                        <NoContentMessage message={`${!stats ? '' : 'Data error'}`} />
                                }
                            </>
                    }
                </TopMainStatsCard>
            </div>

            <div className="row justify-content-center">
                <TopMainStatsCard title='Matches' link='/dashboard/matches' icon='game-icons:soccer-kick'>
                    {
                        matches && matches.totals >= 0
                            ?
                            <DashPastUpcomingCard total={stats ? matches.totals : 0} past={stats ? matches.past : 0} upcoming={stats ? stats.matches.upcoming : 0} />
                            :
                            <>
                                {
                                    loading ?
                                        <Loader />
                                        :
                                        <NoContentMessage message={`${!stats ? '' : 'Data error'}`} />
                                }
                            </>
                    }
                </TopMainStatsCard>
                <TopMainStatsCard title='Predictions' link='/dashboard/predictions' icon='mdi:soccer-field'>
                    {
                        predictions && predictions.totals >= 0
                            ?
                            <DashPastUpcomingCard total={stats ? predictions.totals : 0} past={stats ? predictions.past : 0} upcoming={stats ? stats.predictions.upcoming : 0} />
                            :
                            <>
                                {
                                    loading ?
                                        <Loader />
                                        :
                                        <NoContentMessage message={`${!stats ? '' : 'Data error'}`} />
                                }
                            </>
                    }
                </TopMainStatsCard>
                <TopMainStatsCard title='Odds' link='/dashboard/odds' icon='mdi:soccer-field'>
                    {
                        odds && odds.totals >= 0
                            ?
                            <DashPastUpcomingCard total={stats ? odds.totals : 0} past={stats ? odds.past : 0} upcoming={stats ? odds.upcoming : 0} />
                            :
                            <>
                                {
                                    loading ?
                                        <Loader />
                                        :
                                        <NoContentMessage message={`${!stats ? '' : 'Data error'}`} />
                                }
                            </>
                    }
                </TopMainStatsCard>
            </div>
        </div>
    )
}

export default TopMainStats