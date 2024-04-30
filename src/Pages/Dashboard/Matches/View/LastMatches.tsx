import TeamMatchesCard from '@/components/Teams/TeamMatchesCard'
import useAxios from '@/hooks/useAxios'
import { GameInterface } from '@/interfaces/FootballInterface'
import Composer from '@/utils/Composer'
import { teamLogo } from '@/utils/helpers'
import { useEffect, useState } from 'react'

type Props = {
    game: GameInterface
    homeTeam: any
    awayTeam: any
    perPage?: number | string
    currentground?: boolean
    setHomeTeamRecentResults?: React.Dispatch<React.SetStateAction<string[]>>
    setAwayTeamRecentResults?: React.Dispatch<React.SetStateAction<string[]>>
    withUpcoming?: boolean
}

const LastMatches = ({ game, homeTeam, awayTeam, currentground, perPage, setHomeTeamRecentResults, setAwayTeamRecentResults, withUpcoming }: Props) => {

    const { get: getHomeTeamGames } = useAxios()
    const { get: getAwayTeamGames } = useAxios()
    const [homeTeamGames, setHomeTeamGames] = useState<GameInterface[]>()
    const [awayTeamGames, setAwayTeamGames] = useState<GameInterface[]>()

    // Getting homeTeam games
    useEffect(() => {

        if (homeTeam) {
            getHomeTeamGames(`dashboard/teams/view/${homeTeam.id}/matches?type=past&with_upcoming=${withUpcoming || ''}&upcoming_limit=2&per_page=${perPage || 15}&to_date=${game?.utc_date}&before_to_date=1&reverse_order=1${currentground ? '&currentground=home' : ''}`).then((res) => {

                const { data } = res
                if (data) {
                    setHomeTeamGames(data)
                }
            })
        }
    }, [homeTeam])

    // Getting awayTeam games
    useEffect(() => {

        if (awayTeam) {
            getAwayTeamGames(`dashboard/teams/view/${awayTeam.id}/matches?type=past&with_upcoming=${withUpcoming || ''}&upcoming_limit=2&per_page=${perPage || 15}&to_date=${game?.utc_date}&before_to_date=1${currentground ? '&currentground=away' : ''}`).then((res) => {

                const { data } = res
                if (data) {
                    setAwayTeamGames(data)
                }
            })
        }
    }, [awayTeam])

    return (
        <div>
            <div className="card mb-5">
                <div className="card-body">
                    <div className="row gap-4 gap-md-0">
                        <div className="col-12"><h5 className='rounded text-center d-flex justify-content-between'><span className='d-flex gap-1 align-items-center'><img className='symbol-image-xm' src={teamLogo(homeTeam.logo)} alt="" />{Composer.team(homeTeam, 'TLA')}</span><span>{currentground ? 'Home/Away' : 'Past matches'}</span><span className='d-flex gap-1 align-items-center'><img className='symbol-image-xm' src={teamLogo(awayTeam.logo)} alt="" />{Composer.team(awayTeam, 'TLA')}</span></h5></div>
                        {/* Home Card */}
                        <div className='col-md-6'>
                            <TeamMatchesCard team={homeTeam} teamGames={homeTeamGames} setTeamRecentResults={setHomeTeamRecentResults} />
                        </div>
                        {/* Away Card */}
                        <div className='col-md-6'>
                            <TeamMatchesCard team={awayTeam} teamGames={awayTeamGames} setTeamRecentResults={setAwayTeamRecentResults} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default LastMatches