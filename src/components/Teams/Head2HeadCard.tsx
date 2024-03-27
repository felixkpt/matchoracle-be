import Composer from '@/utils/Composer'
import { NavLink } from 'react-router-dom'
import Loader from '../Loader'
import { GameInterface, TeamInterface } from '@/interfaces/FootballInterface'
import FormatDate from '@/utils/FormatDate'
import FormSummary from './FormSummary'
import { useEffect, useState } from 'react'
import useAxios from '@/hooks/useAxios'
import Select from 'react-select'
import { teamLogo } from '@/utils/helpers'

type Props = {
    game: GameInterface
    homeTeam: TeamInterface
    awayTeam: TeamInterface
    perPage?: number
}

type Head2HeadOptionsType = {
    id: string;
    name: string;
}

const Head2HeadCard = ({ game, homeTeam, awayTeam, perPage }: Props) => {

    const { get: geGames } = useAxios()

    const [games, setGames] = useState<GameInterface[]>()

    const head2HeadOptions: Head2HeadOptionsType[] = [
        { id: 'all', name: `All` },
        { id: 'home-away', name: `${Composer.team(homeTeam, 'TLA')} home, ${Composer.team(awayTeam, 'TLA')} away` },
    ]

    const [selectedHead2HeadOption, setSelectedHead2HeadOption] = useState<Head2HeadOptionsType | null>(head2HeadOptions[0])

    // Getting games
    useEffect(() => {

        if (game) {
            geGames(`admin/matches/view/${game.id}/head2head?type=past&per_page=${perPage || 5}&to_date=${game?.utc_date}&before_to_date=1&playing=${selectedHead2HeadOption?.id || 'all'}&break_preds2=1`).then((res) => {

                const { data } = res
                if (data) {
                    setGames(data)
                }
            })
        }
    }, [game, selectedHead2HeadOption])

    let teamWins = 0
    let draws = 0
    let teamLoses = 0
    let totals = 0

    return (
        <div className="card">
            <div className="card-body">
                <h6>Head to Head</h6>
                <div className="card">
                    <div className="card-header">
                        <div>
                            <Select options={head2HeadOptions}
                                defaultValue={selectedHead2HeadOption}
                                onChange={(val) => setSelectedHead2HeadOption(val)}
                                getOptionValue={(option: any) => `${option['id']}`}
                                getOptionLabel={(option: any) => `${option['name']}`}
                                isSearchable={false}
                            />
                        </div>
                    </div>
                    <div className="card-body">
                        {games ?
                            <div>
                                <div className='cursor-default striped-section'>
                                    {
                                        games.map((game) => {
                                            const home_team = game.home_team
                                            const away_team = game.away_team
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

                                                if (winnerId == homeTeam.id) {
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
                                                        <div className="col-6 d-flex flex-column">
                                                            <div className='col text-nowrap d-flex align-items-center gap-1'><span><img className='symbol-image-xm' src={teamLogo(home_team.logo)} alt="" /></span><span className={`text-nowrap text-truncate ${winningSide === 'h' ? 'fw-medium' : ''}`}>{Composer.team(home_team, 'short')}</span></div>
                                                            <div className='col text-nowrap d-flex align-items-center gap-1'><span><img className='symbol-image-xm' src={teamLogo(away_team.logo)} alt="" /></span><span className={`text-nowrap text-truncate ${winningSide === 'a' ? 'fw-medium' : ''}`}>{Composer.team(away_team, 'short')}</span></div>
                                                        </div>
                                                        <div className='col-2 d-flex flex-column align-items-center border-end border-2 my-1'>
                                                            <div className={`col-5 text-nowrap ${winningSide === 'h' ? 'fw-medium' : ''}`}>{Composer.results(game.score, 'ft', 'h')}</div>
                                                            <div className={`col-5 text-nowrap ${winningSide === 'a' ? 'fw-medium' : ''}`}>{Composer.results(game.score, 'ft', 'a')}</div>
                                                        </div>
                                                    </div>
                                                </NavLink>
                                            )
                                        })
                                    }

                                </div>
                                {
                                    totals > 0 &&
                                    <FormSummary data1={teamWins} data2={draws} data3={teamLoses} totals={totals} data1ColorClass='bg-primary' loseColorClass='bg-secondary' />
                                }
                                <div className='d-flex flex-column'>
                                    <div className='d-flex gap-3 align-items-center justify-content-between'>{homeTeam.name} <div className='rounded bg-primary' style={{ width: 12, height: 12 }}></div></div>
                                    <div className='d-flex gap-3 align-items-center justify-content-between'>Draw <div className='rounded bg-warning' style={{ width: 12, height: 12 }}></div></div>
                                    <div className='d-flex gap-3 align-items-center justify-content-between'>{awayTeam.name} <div className='rounded bg-secondary' style={{ width: 12, height: 12 }}></div></div>
                                </div>
                            </div>
                            :
                            <div className='position-relative'><Loader justify='start' /></div>}
                    </div>
                </div>
            </div>
        </div>
    )
}

export default Head2HeadCard