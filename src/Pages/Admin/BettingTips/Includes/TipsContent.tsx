import FormatDate from '@/utils/FormatDate';
import { GameInterface } from '@/interfaces/FootballInterface';
import { competitionLogo, teamLogo } from '@/utils/helpers';
import GameComposer from '@/utils/Composer';

type Props = {
    data: any
    odds_name: string
    odds_name_print: string
    odds?: number
    outcome?: string
}

function __dangerousHtml(html: HTMLElement) {
    return <div dangerouslySetInnerHTML={{ __html: html }} />;
}

const TipsContent = ({ data, odds_name, odds_name_print, odds, outcome }: Props) => {
    return (
        <div>
            <div className="row border border-0 border-bottom border-dark p-2 bg-body-secondary rounded">
                <div className="col-5 border-dark">Event</div>
                <div className="col-4 border-dark">Tip</div>
                <div className="col-3 border-dark">Outcome</div>
            </div>
            {
                data.map((game: GameInterface) => {

                    return (
                        <div className="row border border-0 border-bottom border-dark">
                            <div className="col-12 text-muted">
                                <small>{FormatDate.toLocaleDateString(game.utc_date)}</small>
                            </div>
                            <div className="col-md-5">
                                <div className="row">
                                    <div className="col-12">
                                        <div className="d-flex flex-column align-items-center">
                                            <div className="d-flex align-items-center w-100 gap-2 text-muted">
                                                <img className="symbol-image-sm border" src={competitionLogo(game.competition.logo)} alt="" />
                                                <small>{game.competition.name}</small>
                                            </div>

                                        </div>
                                        <div className="col d-flex justify-content-center flex-column mt-2">
                                            <div className='col text-nowrap d-flex align-items-center gap-1'><span><img className='symbol-image-xm' src={teamLogo(game.home_team.logo)} alt="" /></span><span className={`text-nowrap text-truncate`}>{GameComposer.team(game.home_team, 'short')}</span></div>
                                            <div className='col text-nowrap d-flex align-items-center gap-1'><span><img className='symbol-image-xm' src={teamLogo(game.away_team.logo)} alt="" /></span><span className={`text-nowrap text-truncate`}>{GameComposer.team(game.away_team, 'short')}</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div className="col-6 col-md-4 d-flex align-items-center">{odds_name_print} @ {game.odds[0][odds_name]}</div>
                            <div className="col-6 col-md-3 d-flex align-items-center">
                                <div className="d-flex align-items-center w-100">
                                    <div className="col-6">
                                        {__dangerousHtml(game.Fulltime)}
                                    </div>
                                    <div>
                                        <div className={`betting-tip-outcome ${game.outcome == 'W' ? 'bg-success text-white' : (game.outcome == 'L' ? 'bg-danger text-white' : 'bg-primary text-white')}`}>{game.outcome}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    )
                })
            }

            {
                odds &&
                <div className='col-12 d-flex justify-content-end mt-1 mb-3'>
                    <div className='d-flex align-items-center justify-content-between gap-2 shadow p-1 rounded'>
                        <div>Total odds <strong className='text-success'>{odds}</strong></div>
                        <div>|</div>
                        <div>
                            {
                                outcome == 'W' ?
                                    <strong className='text-success'>Won</strong>
                                    :
                                    <>
                                        {
                                            outcome == 'L' ?
                                                <strong className='text-danger'>Lost</strong>
                                                :
                                                <strong className='text-primart'>Unsettled</strong>
                                        }
                                    </>
                            }
                        </div>
                    </div>
                </div>
            }

        </div>
    )
}

export default TipsContent