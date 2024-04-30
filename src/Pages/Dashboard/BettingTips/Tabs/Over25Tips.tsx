import Multiples from "../Includes/Multiples"
import Singles from "../Includes/Singles"

type Props = {
    uri: string
}

const Over25Tips = ({ uri }: Props) => {

    const type = 'over_25_tips'
    const odds_name = 'over_25_odds'
    const odds_name_print = 'Over 2.5'

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

export default Over25Tips