import Multiples from "../Includes/Multiples"
import Singles from "../Includes/Singles"

type Props = {
    uri: string
}

const AllTips = ({ uri }: Props) => {

    const type = 'all_tips'

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

export default AllTips