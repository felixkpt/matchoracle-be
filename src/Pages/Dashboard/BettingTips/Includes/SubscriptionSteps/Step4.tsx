import { BettingStrategyInterface } from "@/interfaces/FootballInterface";

type Props = {
    bettingStrategy: BettingStrategyInterface;
    paymentMethod: string
};

const Step4 = ({ bettingStrategy, paymentMethod }: Props) => {

    console.log(bettingStrategy, paymentMethod)

    return (
        <div>
            <div style={{fontSize:'110%'}}>
                You are subscribing to <span className="text-primary">{bettingStrategy.name}</span> using <span className="text-primary">{paymentMethod}</span>...
            </div>
        </div>
    )
}

export default Step4