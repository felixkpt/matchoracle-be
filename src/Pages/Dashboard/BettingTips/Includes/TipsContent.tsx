import BetslipGames from './BetslipGames';
import BetslipInfo from './BetslipInfo';

type Props = {
    data: any
}

const TipsContent = ({ data }: Props) => {

    const betslip = data.betslip
    const odds = data.odds
    return (
        <div>
            <BetslipGames betslip={betslip} />
            {
                odds &&
                <BetslipInfo data={data} />
            }

        </div>
    )
}

export default TipsContent