import FormSummary from "@/components/Teams/FormSummary"
import { GameInterface } from "@/interfaces/FootballInterface"
import Composer from "@/utils/Composer"

type Props = {
    game: GameInterface
}

const PredictionsSection = ({ game }: Props) => {
    const { formatted_prediction } = game

    return (
        <div>

            <div className="card shadow">
                <div className="card-header"><h5>Predictions for {Composer.team(game.home_team)} vs {Composer.team(game.away_team)}</h5></div>
                <div className="card-body">
                    <div className="row">
                        <div className="col-12 col-md-12 col-lg-4">
                            Win-Draw-Win: {formatted_prediction?.cs ? `(${formatted_prediction.cs})` : ''}
                            {
                                formatted_prediction ?
                                    <FormSummary teamWins={formatted_prediction.home_win_proba} draws={formatted_prediction.draw_proba} teamLoses={formatted_prediction.away_win_proba} totals={100} winColorClass="bg-primary" labelA=" (1)" labelB=" (X)" labelC=" (2)" />
                                    : ' N/A'
                            }
                        </div>
                        <div className="col-12 col-md-12 col-lg-4">
                            BTS:
                            {
                                formatted_prediction ?
                                    <FormSummary teamWins={formatted_prediction.gg_proba} draws={formatted_prediction.ng_proba} totals={100} winColorClass="bg-primary" labelA=" (YES)" labelB=" (NO)" />
                                    : ' N/A'
                            }
                        </div>
                        <div className="col-12 col-md-12 col-lg-4">
                            OVER/UNDER 25:
                            {
                                formatted_prediction ? <FormSummary teamWins={formatted_prediction.over25_proba} draws={formatted_prediction.under25_proba} totals={100} winColorClass="bg-primary" labelA=" (OVER)" labelB=" (UNDER)" /> : ' N/A'
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>

    )
}

export default PredictionsSection