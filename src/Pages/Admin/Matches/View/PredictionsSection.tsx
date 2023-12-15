import FormSummary from "@/components/Teams/FormSummary"
import { GameInterface } from "@/interfaces/FootballInterface"
import Composer from "@/utils/Composer"
import Str from "@/utils/Str"

type Props = {
    game: GameInterface
}

export function __dangerousHtml(html: HTMLElement| string) {
    // Implement the logic to safely render HTML content here
    return <div dangerouslySetInnerHTML={ { __html: html } } />;
}


const PredictionsSection = ({ game }: Props) => {
    const { prediction: formatted_prediction } = game

    return (
        <div>

            <div className="card shadow">
                <div className="card-header"><h5>Predictions for {Composer.team(game.home_team)} vs {Composer.team(game.away_team)}</h5></div>
                <div className="card-body">
                    <div className="row">
                        <div className="col-12 col-md-12 col-lg-4">
                           {__dangerousHtml(` Win-Draw-Win: ${game.CS}`)}
                            {
                                formatted_prediction ?
                                    <FormSummary data1={formatted_prediction.home_win_proba} data2={formatted_prediction.draw_proba} data3={formatted_prediction.away_win_proba} totals={100} data1ColorClass="bg-primary" label1=" (1)" label2=" (X)" label3=" (2)" />
                                    : ' N/A'
                            }
                        </div>
                        <div className="col-12 col-md-12 col-lg-4">
                            <div className="border-2 py-1 text-nowrap">BTS:</div>
                            {
                                formatted_prediction ?
                                    <FormSummary data1={formatted_prediction.gg_proba} data2={formatted_prediction.ng_proba} totals={100} data1ColorClass="bg-primary" label1=" (YES)" label2=" (NO)" />
                                    : ' N/A'
                            }
                        </div>
                        <div className="col-12 col-md-12 col-lg-4">
                            <div className="border-2 py-1 text-nowrap">OVER/UNDER 25:</div>
                            {
                                formatted_prediction ? <FormSummary data1={formatted_prediction.over25_proba} data2={formatted_prediction.under25_proba} totals={100} data1ColorClass="bg-primary" label1=" (OVER)" label2=" (UNDER)" /> : ' N/A'
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>

    )
}

export default PredictionsSection