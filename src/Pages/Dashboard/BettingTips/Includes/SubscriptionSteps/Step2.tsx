import { BettingStrategyInterface } from '@/interfaces/FootballInterface'

type Props = {
    bettingStrategies: BettingStrategyInterface[]
    bettingStrategy: BettingStrategyInterface
    setBettingStrategy: React.Dispatch<React.SetStateAction<BettingStrategyInterface | null>>;
}

const Step2 = ({ bettingStrategies, bettingStrategy, setBettingStrategy }: Props) => {

    return (
        <div>
            <h6 className='text-muted'>Choose your strategy!</h6>
            <div className="row row-cols-1 row-cols-md-3 mb-3 text-center">
                {bettingStrategies.map((strategy) => (
                    <div key={strategy.id} className="col">
                        <div onClick={() => setBettingStrategy(strategy)} className={`card mb-4 rounded-3 shadow-sm cursor-pointer ${strategy.id == bettingStrategy.id ? 'border-primary' : ''}`}>
                            <div className={`card-header py-3 ${strategy.id == bettingStrategy.id ? 'text-bg-primary border-primary' : ''}`}>
                                <h4 className="my-0 fw-normal">{strategy.name}</h4>
                            </div>
                            <div className="card-body">
                                <h1 className="card-title pricing-card-title">${strategy.amount}<small className="text-muted fw-light">/mo</small></h1>
                                <ul className="list-unstyled mt-3 mb-4">
                                    {strategy.advantages && strategy.advantages.map((advantage, index) => (
                                        <li key={index}>{advantage}</li>
                                    ))}
                                </ul>
                                <button
                                    type="button"
                                    className={`w-100 btn btn-lg ${strategy.id == bettingStrategy.id ? 'btn-primary' : 'btn-outline-primary'}`}
                                >
                                    {strategy.slogan}
                                </button>
                            </div>
                        </div>
                    </div>
                )
                )}
            </div>
        </div>
    )
}

export default Step2