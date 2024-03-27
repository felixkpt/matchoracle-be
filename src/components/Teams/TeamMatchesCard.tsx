import Composer from '@/utils/Composer'
import ResultsIcon from './ResultsIcon'
import { NavLink } from 'react-router-dom'
import Loader from '../Loader'
import { TeamInterface } from '@/interfaces/FootballInterface'
import { GameInterface } from '@/interfaces/FootballInterface'
import FormatDate from '@/utils/FormatDate'
import FormSummary from './FormSummary'
import { useEffect, useState } from 'react'
import { teamLogo } from '@/utils/helpers'

type Props = {
    team: TeamInterface
    teamGames: GameInterface[] | undefined
    setTeamRecentResults?: React.Dispatch<React.SetStateAction<string[]>>
}

const TeamMatchesCard = ({ team, teamGames, setTeamRecentResults }: Props) => {

    let teamWins = 0
    let draws = 0
    let teamLoses = 0
    let totals = 0

    const [resultsForm, setResultsForm] = useState<string[]>([])

    useEffect(() => {

        if (team && setTeamRecentResults) {
            // reset team results
            setResultsForm([])

            if (teamGames) {

                teamGames.map((game, i) => {
                    const resultsStatus = Composer.winner(game, team.id)
                    const hasResults = Composer.hasResults(game)

                    if (hasResults) {
                        totals++
                        setResultsForm((curr) => [...curr, resultsStatus])
                    }
                })

            }
        }

    }, [team, teamGames, setTeamRecentResults])

    useEffect(() => {

        setTimeout(() => {
            if (resultsForm.length > 0 && setTeamRecentResults) {
                setTeamRecentResults(resultsForm)
            }
        }, 150);

    }, [resultsForm])

    return (
        <div className="card">
            <div className="card-header">
                <h6>{team.name}</h6>
            </div>
            <div className="card-body">
                {teamGames ?
                    <div>
                        <div className='cursor-default striped-section'>
                            {
                                teamGames.map((game) => {
                                    const home_team = game.home_team
                                    const away_team = game.away_team
                                    const resultsStatus = Composer.winner(game, team.id)
                                    const winningSide = Composer.winningSide(game)
                                    const winnerId = Composer.winnerId(game)
                                    const hasResults = Composer.hasResults(game)

                                    if (hasResults) {
                                        totals++
                                    }

                                    if (winningSide === 'D') {
                                        draws++
                                    }
                                    else if (winningSide === 'h' || winningSide === 'a') {

                                        if (winnerId == team.id) {
                                            teamWins++
                                        }
                                        else {
                                            teamLoses++
                                        }
                                    }

                                    return (
                                        <NavLink key={game.id} to={`/admin/matches/view/${game.id}`} className={`text-decoration-none text-dark`}>
                                            <div className='row py-1'>
                                                <div className="col-3 d-flex flex-column align-items-center border-end border-2 my-1 fs-small"><span>{FormatDate.DDMMYY(game.utc_date)}</span><span>{FormatDate.HHMM(game.utc_date)}</span></div>
                                                <div className="col-5 d-flex flex-column">
                                                    <div className='col text-nowrap d-flex align-items-center gap-1'><span><img className='symbol-image-xm' src={teamLogo(home_team.logo)} alt="" /></span><span className={`text-nowrap text-truncate ${winningSide === 'h' ? 'fw-medium' : ''}`}>{Composer.team(home_team, 'short')}</span></div>
                                                    <div className='col text-nowrap d-flex align-items-center gap-1'><span><img className='symbol-image-xm' src={teamLogo(away_team.logo)} alt="" /></span><span className={`text-nowrap text-truncate ${winningSide === 'a' ? 'fw-medium' : ''}`}>{Composer.team(away_team, 'short')}</span></div>
                                                </div>
                                                <div className='col-2 d-flex flex-column align-items-center border-end border-2 my-1'>
                                                    <div className={`col-5 text-nowrap ${winningSide === 'h' ? 'fw-medium' : ''}`}>{Composer.results(game.score, 'ft', 'h')}</div>
                                                    <div className={`col-5 text-nowrap ${winningSide === 'a' ? 'fw-medium' : ''}`}>{Composer.results(game.score, 'ft', 'a')}</div>
                                                </div>
                                                <div className="col-2 d-flex align-items-center justify-content-center">
                                                    <ResultsIcon winner={resultsStatus} />
                                                </div>
                                            </div>
                                        </NavLink>
                                    )
                                })
                            }

                        </div>
                        <FormSummary data1={teamWins} data2={draws} data3={teamLoses} totals={totals} />
                    </div>
                    :
                    <div className='position-relative'><Loader /></div>}
            </div>
        </div>)
}

export default TeamMatchesCard