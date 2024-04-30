import React from 'react'

type Props = {
    data: any
}

const BetslipInfo = ({ data }: Props) => {
    const odds = data.odds
    const stake = data.stake
    const outcome = data.outcome
    const bankroll_deposits = data.bankroll_deposits
    const final_bankroll = data.final_bankroll
    const total_gains = data.total_gains

    return (
        <div>
            <div className='col-12 mt-2 mb-4 investment-summary w-100'>
                <div className='shadow py-1 rounded row align-items-center text-muted'>
                    <div className="stake-details">
                        <div className="d-flex gap-1 justify-content-between">
                            <div className="d-flex gap-3">
                                <small>Odds <strong className='text-success'>{odds}</strong></small>
                                <span>|</span>
                                <small>Stake <strong className='text-success'>{stake}</strong></small>
                            </div>
                            <div className="d-flex flex-column">
                                <small>Status:</small>
                                {
                                    outcome == 'W' ?
                                        <small className='text-success'>Won</small>
                                        :
                                        <>
                                            {
                                                outcome == 'L' ?
                                                    <small className='text-danger'>Lost</small>
                                                    :
                                                    <small className='text-primart'>Unsettled</small>
                                            }
                                        </>
                                }
                            </div>
                        </div>
                    </div>
                    <div className='brankroll-details'>
                        <div className='d-flex gap-1 align-items-center justify-content-between'>
                            <div className="col-7">
                                <div className="d-flex flex-column">
                                    <small>Bankroll: {final_bankroll}</small>
                                    <small>Deposited: {bankroll_deposits}</small>
                                </div>
                            </div>
                            <span className='col-1'>|</span>
                            <div className="col-4">
                                <div className="d-flex flex-column">
                                    <small>Total gain: {total_gains}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default BetslipInfo