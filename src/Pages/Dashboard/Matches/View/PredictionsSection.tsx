import FormSummary from "@/components/Teams/FormSummary"
import { GameInterface } from "@/interfaces/FootballInterface"
import Composer from "@/utils/Composer"
import OverUnderVotesSection from "./Includes/OverUnderVotesSection"
import BTSVotesSection from "./Includes/BTSVotesSection"
import { renderCS } from "@/components/HtmlRenderers"

type Props = {
    game: GameInterface
}

export function __dangerousHtml(html: HTMLElement | string) {
    // Implement the logic to safely render HTML content here
    return <div dangerouslySetInnerHTML={{ __html: html }} />;
}

const PredictionsSection = ({ game }: Props) => {
    const { prediction_strategy } = game

    return (
        <div>
            <div className="card shadow-sm mb-3">
                <div className="card-header"><h5>Predictions for {Composer.team(game.home_team)} vs {Composer.team(game.away_team)}</h5></div>
                <div className="card-body">
                    <div className="row">
                        <div className="col-12 col-md-12 col-lg-4">
                            <span>Win-Draw-Win: {renderCS('', game)}</span>
                            {
                                prediction_strategy ?
                                    <FormSummary data1={prediction_strategy.ft_home_win_proba} data2={prediction_strategy.ft_draw_proba} data3={prediction_strategy.ft_away_win_proba} totals={100} data1ColorClass="bg-primary" label1=" (1)" label2=" (X)" label3=" (2)" />
                                    : ' N/A'
                            }
                        </div>
                        <div className="col-12 col-md-12 col-lg-4">
                            <div className="border-2 py-1 text-nowrap">OVER/UNDER 25:</div>
                            {
                                prediction_strategy ? <FormSummary data1={prediction_strategy.over25_proba} data2={prediction_strategy.under25_proba} totals={100} data1ColorClass="bg-primary" label1=" (OVER)" label2=" (UNDER)" /> : ' N/A'
                            }
                        </div>
                        <div className="col-12 col-md-12 col-lg-4">
                            <div className="border-2 py-1 text-nowrap">BTS:</div>
                            {
                                prediction_strategy ?
                                    <FormSummary data1={prediction_strategy.gg_proba} data2={prediction_strategy.ng_proba} totals={100} data1ColorClass="bg-primary" label1=" (YES)" label2=" (NO)" />
                                    : ' N/A'
                            }
                        </div>
                    </div>
                </div>
            </div>
            <div className="card shadow-sm mb-3">
                <div className="card-header"><h6>More votes</h6></div>
                <div className="card-body">
                    <div className="row">
                        <div className="col-md-6"><div className="mx-1"><OverUnderVotesSection game={game} /></div></div>
                        <div className="col-md-6"><div className="mx-1"><BTSVotesSection game={game} /></div></div>
                    </div>
                </div>
            </div>
        </div>

    )
}

export default PredictionsSection