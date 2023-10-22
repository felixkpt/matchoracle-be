import Composer from '@/utils/Composer'
import React from 'react'
import ResultsIcon from './ResultsIcon'
import { NavLink } from 'react-router-dom'
import Loader from './Loader'
import { TeamInterface } from '@/interfaces/FootballInterface'
import { GameInterface } from '@/interfaces/FootballInterface'
import FormatDate from '@/utils/FormatDate'

type Props = {
    team: TeamInterface
    teamGames: GameInterface[] | undefined
    context: 'h' | 'a'
}

const TeamMatchesCard = ({ team, teamGames }: Props) => {

    return (
        <div className="card">
            <div className="card-header">
                <h6>{team.name}</h6>
            </div>
            <div className="card-body">
                {teamGames ?
                    <div className='cursor-default striped-section'>
                        {
                            teamGames.map((game) => {
                                const home_team = game.home_team
                                const away_team = game.away_team
                                const winner = Composer.winner(game, team.id)
                                const winningSide = Composer.winningSide(game)

                                return (
                                    <NavLink key={game.id} to={`/admin/matches/view/${game.id}`} className={`text-decoration-none text-dark`}>
                                        <div className='row py-1'>
                                            <div className="col-3 d-flex flex-column align-items-center border-end border-2 my-1 fs-small"><span>{FormatDate.DDMMYY(game.utc_date)}</span><span>{FormatDate.HHMM(game.utc_date)}</span></div>
                                            <div className="col-5 d-flex flex-column">
                                                <div className='col text-nowrap d-flex align-items-center gap-1'><span><img className='symbol-image-xm' src={home_team.crest} alt="" /></span><span className={`text-nowrap text-truncate ${winningSide === 'h' ? 'fw-medium' : ''}`}>{Composer.team(home_team, 'short')}</span></div>
                                                <div className='col text-nowrap d-flex align-items-center gap-1'><span><img className='symbol-image-xm' src={away_team.crest} alt="" /></span><span className={`text-nowrap text-truncate ${winningSide === 'a' ? 'fw-medium' : ''}`}>{Composer.team(away_team, 'short')}</span></div>
                                            </div>
                                            <div className='col-2 d-flex flex-column align-items-center border-end border-2 my-1'>
                                                <div className={`col-5 text-nowrap ${winningSide === 'h' ? 'fw-medium' : ''}`}>{Composer.results(game.score, 'ft', 'h')}</div>
                                                <div className={`col-5 text-nowrap ${winningSide === 'a' ? 'fw-medium' : ''}`}>{Composer.results(game.score, 'ft', 'a')}</div>
                                            </div>
                                            <div className="col-2 d-flex align-items-center justify-content-center">
                                                <ResultsIcon winner={winner} />
                                            </div>
                                        </div>
                                    </NavLink>
                                )
                            })
                        }
                    </div>
                    :
                    <div className='position-relative'><Loader message='Loading' /></div>}
            </div>
        </div>)
}

export default TeamMatchesCard