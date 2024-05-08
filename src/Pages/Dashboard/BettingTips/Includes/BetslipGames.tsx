import { GameInterface } from "@/interfaces/FootballInterface";
import GameComposer from "@/utils/Composer";
import FormatDate from "@/utils/FormatDate";
import { competitionLogo, teamLogo } from "@/utils/helpers";
import { NavLink } from "react-router-dom";

type Props = {
    betslip: [{
        game: GameInterface,
        odds_name: 'home_win_odds' |
        'draw_odds' |
        'away_win_odds' |
        'over_25_odds' |
        'under_25_odds' |
        'gg_odds' |
        'ng_odds',
        odds_name_print: string,
    }]
}

function __dangerousHtml(html: HTMLElement) {
    return <div dangerouslySetInnerHTML={{ __html: html }} />;
}

const BetslipGames = ({ betslip }: Props) => {

    return (
        <div>
            <div className="row border border-0 border-bottom border-dark p-2 bg-body-secondary rounded">
                <div className="col-5 border-dark">Event</div>
                <div className="col-4 border-dark">Tip</div>
                <div className="col-3 border-dark">Result</div>
            </div>
            {
                betslip.map((slip) => {
                    const game = slip.game
                    const odds_name_print = slip.odds_name_print

                    return (
                        <div key={game.id} className="row border border-0 border-bottom border-dark pb-1">
                            <div className="col-12 text-muted">
                                <div className="d-flex gap-2 justify-content-between py-1">
                                    <small>{FormatDate.toLocaleDateString(game.utc_date)}</small>
                                    {
                                        game.is_subscribed === false
                                            ?
                                            <>
                                                <button type="button" className="btn btn-link ntn-sm py-0" data-bs-toggle="modal" id={`SubscribeButtonTrigger`} data-bs-target={`#SubscribeModal`}><small>See how to subscribe!</small></button>
                                            </>
                                            : null
                                    }
                                </div>
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
                            <div className="col-5 col-md-4 d-flex align-items-center">{odds_name_print} @ {game.odds}</div>
                            <div className="col-2 d-md-none">
                            </div>
                            <div className="col-5 col-md-3 d-flex align-items-center">
                                <div className="scores-outcome d-flex align-items-center justify-content-center w-100">
                                    <div className="score">
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
        </div>
    )
}

export default BetslipGames