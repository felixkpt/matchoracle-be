import Multiples from "../Includes/Multiples"
import Singles from "../Includes/Singles"

type Props = {
    uri: string
}

const HomeWinTips = ({ uri }: Props) => {

    const type = 'home_win_tips'
    const odds_name = 'home_win_odds'
    const odds_name_print = 'Home win'

    return (
        <div className="mt-3">
            <div className="row gap-5 gap-md-0">
                <div className="col-md-6">
                    <Singles uri={uri} type={type} />
                </div>
                <div className="col-md-6">
                    <Multiples uri={uri} type={type} />
                </div>
            </div>
        </div>
    )
}

export default HomeWinTips