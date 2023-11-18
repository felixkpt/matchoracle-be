import FormSummary from "@/components/Teams/FormSummary"
import { GameInterface } from "@/interfaces/FootballInterface"
import Composer from "@/utils/Composer"

type Props = {
    game: GameInterface
}

const PredictionsSection = ({ game }: Props) => {
    const { prediction } = game

    return (
        <div>

            <div className="card shadow">
                <div className="card-header"><h5>Predictions for {Composer.team(game.home_team)} vs {Composer.team(game.away_team)}</h5></div>
                <div className="card-body">
                    <div className="row">
                        <div className="col-12 col-md-12 col-lg-4">
                            Win-Draw-Win: {prediction?.cs ? `(${prediction.cs})` : ''}
                            {
                                prediction ?
                                    <FormSummary teamWins={prediction.home_win_proba} draws={prediction.draw_proba} teamLoses={prediction.away_win_proba} totals={100} winColorClass="bg-primary" labelA=" (1)" labelB=" (X)" labelC=" (2)" />
                                    : ' N/A'
                            }
                        </div>
                        <div className="col-12 col-md-12 col-lg-4">
                            BTS:
                            {
                                prediction ?
                                    <FormSummary teamWins={prediction.gg_proba} draws={prediction.ng_proba} totals={100} winColorClass="bg-primary" labelA=" (YES)" labelB=" (NO)" />
                                    : ' N/A'
                            }
                        </div>
                        <div className="col-12 col-md-12 col-lg-4">
                            OVER/UNDER 25:
                            {
                                prediction ? <FormSummary teamWins={prediction.over25_proba} draws={prediction.under25_proba} totals={100} winColorClass="bg-primary" labelA=" (OVER)" labelB=" (UNDER)" /> : ' N/A'
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>

    )
}

export default PredictionsSection