import React from 'react'

type Props = {
    investment: any
}

const InvestmentCard = ({ investment }: Props) => {
    return (
        <div className="row p-2 bg-body-secondary rounded cursor-default">
            <div className='col-md-4 d-flex'>
                <span>Total <strong>{investment.total}</strong></span>
            </div>
            <div className="col-md-4 col-xxl-4">
                <span>Initial bankroll <strong>{investment.initial_bankroll}</strong></span>
            </div>
            <div className='col-md-4 d-flex gap-2 justify-content-md-end'>
                <span>Won <strong className="text-warning">{investment.won}</strong></span>
                <span className="text-success">({investment.won_percentage}%)</span>
            </div>
            <div className='col-12 mt-3'>
                <div className="row">
                    <div className='col-md-4 d-flex'>
                        <span title='Average Won Odds'>AVG Won Odds <span className="text-success">{investment.average_won_odds}</span></span>
                    </div>
                    <div className='col-md-4 d-flex'>
                        <span>Gain <span className="text-success">{investment.gain}</span></span>
                    </div>
                    <div className='col-md-4 d-flex justify-content-md-end'>
                        <span title='Return On Investment'>ROI <span className="text-success">{investment.roi}%</span></span>
                    </div>
                </div>
            </div>
            <div className='col-12 mt-3'>
                <div className="row">
                    <div className="col-md-4 col-xxl-4 d-flex">
                        <span>Final bankroll <strong className="text-success">{investment.final_bankroll}</strong></span>
                    </div>

                    <div className="col-md-4 col-xxl-4">
                        <span>Longest winning <strong>{investment.longest_winning_streak}</strong></span>
                    </div>
                    <div className="col-md-4 col-xxl-4 d-flex justify-content-md-end">
                        <span>Longest losing <strong className="text-success">{investment.longest_losing_streak}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default InvestmentCard