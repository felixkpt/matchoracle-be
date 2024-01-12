import React from 'react'

type Props = {
    investment: any
}

const InvestmentCard = ({ investment }: Props) => {
    return (
        <div className="row p-2 bg-body-secondary rounded">
            <div className='col-md-5 d-flex'>
                <span>Total <strong>{investment.total}</strong></span>
            </div>
            <div className='col-md-4 d-flex gap-2'>
                <span>Won <strong className="text-warning">{investment.won}</strong></span>
                <span className="text-success">({investment.won_percentage}%)</span>
            </div>
            <div className='col-md-3 d-flex'>
                <span>Gain <span className="text-success">{investment.gain}</span></span>
            </div>
            <div className='col-12 mt-3'>
                <div className="row">
                    <div className="col-md-5 col-xxl-9">
                        <span>Initial bankroll <strong>{investment.initial_bankroll}</strong></span>
                    </div>
                    <div className="col-md-6 col-xxl-3 d-flex">
                        <span>Final bankroll <strong className="text-success">{investment.final_bankroll}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default InvestmentCard