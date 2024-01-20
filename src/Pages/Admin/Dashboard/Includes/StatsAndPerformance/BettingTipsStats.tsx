import { BettingTipsStatisticsInterface, BettingTipsTotalsInterface } from '@/interfaces/FootballInterface'
import Str from '@/utils/Str'
import { Icon } from '@iconify/react/dist/iconify.js'

import { NavLink } from 'react-router-dom'

type Props = {
    stats: {
        'all': BettingTipsStatisticsInterface[]
        'totals': BettingTipsTotalsInterface
    }
}

const BettingTipsStats = ({ stats }: Props) => {

    const localStats = stats.all
    const totals = stats.totals

    const tipClasses = [
        'home_win_tips',
        'away_win_tips',
        'draw_tips',
        'gg_tips',
        'ng_tips',
        'over_25_tips',
        'under_25_tips',
    ]

    return (
        <div className="card shadow">
            <div className="card-header bg-secondary text-white">
                <h5 className='d-flex align-items-center gap-1'>
                    <Icon width={'2rem'} icon={`${'mdi:chart-line-variant'}`} />
                    <span>Betting tips stats in {localStats[0].range}</span>
                </h5>
            </div>
            <div className="card-body">
                <div className="col-12 mb-4">
                    <div className="table-responsive">

                        <table className='table table-striped'>
                            <thead>
                                <tr className='border-0 border-bottom border-1 border-dark-subtle'>
                                    <th className='col fw-medium'>Type</th>
                                    <th className='col fw-medium'>Total</th>
                                    <th className='col fw-medium text-warning'>Won</th>
                                    <th className='col fw-medium'>Gain</th>
                                    <th className='col fw-medium text-success'>ROI (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                {
                                    localStats.map(
                                        (stat) =>
                                            <tr key={stat.id} className="border-0 border-bottom">
                                                <td className='py-2 col fw-medium'>{Str.title(Str.before(stat.type, '_tips'))}{stat.is_multiples ? '(multi)' : ''}</td>
                                                <td className='col'>{stat.total}</td>
                                                <td className='col text-warning'>{stat.won}</td>
                                                <td className='col'>{stat.gain}</td>
                                                <td className='col text-success'>{stat.roi}</td>
                                            </tr>
                                    )
                                }
                                <tr className="border-0 border-top border-2 border-dark-subtle">
                                    <td className='py-2 col fw-medium'>Grand Totals:</td>
                                    <td className='col'>{totals.total_totals}</td>
                                    <td className='col text-warning'>{totals.total_won}</td>
                                    <td className='col'>{totals.total_gain}</td>
                                    <td className='col text-success'>{totals.average_roi}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div className="row justify-content-end">
                        <NavLink to={'/admin/betting-tips/stats'}><button className='btn btn-outline-dark w-100'>View More</button></NavLink>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default BettingTipsStats