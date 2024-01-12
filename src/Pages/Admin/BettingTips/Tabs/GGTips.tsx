import Multiples from "../Includes/Multiples"
import Singles from "../Includes/Singles"

type Props = {
    uri: string
}

const GGTips = ({ uri }: Props) => {

    const type = 'gg_tips'
    const odds_name = 'gg_odds'
    const odds_name_print = 'BTS - Yes'

    return (
        <div className="mt-3">
            <div className="row gap-5 gap-md-0">
                <div className="col-md-6">
                    <Singles uri={uri} type={type} odds_name={odds_name} odds_name_print={odds_name_print} />
                </div>
                <div className="col-md-6">
                    <Multiples uri={uri} type={type} odds_name={odds_name} odds_name_print={odds_name_print} />
                </div>
            </div>
        </div>
    )
}

export default GGTips