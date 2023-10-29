import TeamMatchesCard from '@/components/Teams/TeamMatchesCard'
import useAxios from '@/hooks/useAxios'
import { GameInterface } from '@/interfaces/FootballInterface'
import Composer from '@/utils/Composer'
import { useEffect, useState } from 'react'

type Props = {
    game: GameInterface
    homeTeam: any
    awayTeam: any
    perPage?: number | string
    currentground?: boolean
    setHomeTeamRecentResults?: React.Dispatch<React.SetStateAction<string[]>>
    setAwayTeamRecentResults?: React.Dispatch<React.SetStateAction<string[]>>
}

const LastMatches = ({ game, homeTeam, awayTeam, currentground, perPage, setHomeTeamRecentResults, setAwayTeamRecentResults }: Props) => {

    const { get: getHomeTeamGames } = useAxios()
    const { get: getAwayTeamGames } = useAxios()
    const [homeTeamGames, setHomeTeamGames] = useState<GameInterface[]>()
    const [awayTeamGames, setAwayTeamGames] = useState<GameInterface[]>()

    // Getting homeTeam games
    useEffect(() => {

        if (homeTeam) {
            getHomeTeamGames(`admin/teams/view/${homeTeam.id}/matches?type=played&per_page=${perPage || 15}&before=${game?.utc_date}${currentground ? '&currentground=home' : ''}`).then((res) => {

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
            getAwayTeamGames(`admin/teams/view/${awayTeam.id}/matches?type=played&per_page=${perPage || 15}&before=${game?.utc_date}${currentground ? '&currentground=away' : ''}`).then((res) => {

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
                    <div className="row">
                        <div className="col-12"><h5 className='rounded text-center d-flex justify-content-between'><span className='d-flex gap-1 align-items-center'><img className='symbol-image-xm' src={homeTeam.crest} alt="" />{Composer.team(homeTeam, 'TLA')}</span><span>{currentground ? 'Home/Away' : 'Past matches'}</span><span className='d-flex gap-1 align-items-center'><img className='symbol-image-xm' src={awayTeam.crest} alt="" />{Composer.team(awayTeam, 'TLA')}</span></h5></div>
                        {/* Home Card */}
                        <div className='col-6'>
                            <TeamMatchesCard team={homeTeam} teamGames={homeTeamGames} setTeamRecentResults={setHomeTeamRecentResults} />
                        </div>
                        {/* Away Card */}
                        <div className='col-6'>
                            <TeamMatchesCard team={awayTeam} teamGames={awayTeamGames} setTeamRecentResults={setAwayTeamRecentResults} />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default LastMatches