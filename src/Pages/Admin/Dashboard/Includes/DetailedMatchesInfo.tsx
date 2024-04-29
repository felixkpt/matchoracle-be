import { MatchesInterface, TodayMatchesInterface } from "@/interfaces/FootballInterface";
import { Icon } from "@iconify/react/dist/iconify.js"

interface MatchesCardProps {
    stats: {
        today: TodayMatchesInterface;
        all: MatchesInterface;
    };
}

const DetailedMatchesInfo: React.FC<MatchesCardProps> = ({ stats }) => {

    const renderStatBlock = (label: string, today: number, allTime: number, icon: string, colorClass: string) => (
        <div className={`d-flex align-items-center justify-content-between shadow-sm p-2 rounded ${colorClass}`}>
            <div className='d-flex align-items-center gap-1 col-6'>
                <Icon width={'1rem'} icon={icon} />
                {label}:
            </div>
            <div className="row col-6">
                <div className="col-6">{today}</div>
                <div className="col-6">{allTime}</div>
            </div>
        </div>
    );

    return (
        <div>
            <div className='d-flex align-items-center justify-content-between shadow-sm p-2 rounded text-muted'>
                <div className='d-flex align-items-center gap-1 col-6'>
                    <h6>Description</h6>
                </div>
                <div className="row col-6">
                    <h6 className='col-6'>Today</h6>
                    <h6 className='col-6'>All time</h6>
                </div>
            </div>
            <div className="d-flex flex-column gap-2 mt-3">
                {renderStatBlock('Matches', stats.today.totals, stats.all.totals, 'ic:sharp-published-with-changes', 'text-success')}
                {renderStatBlock('Played', stats.today.past, stats.all.past, 'mdi:trophy', 'text-info')}
                {renderStatBlock('Fixtures', stats.today.upcoming, stats.all.upcoming, 'fa-solid:running', 'text-warning')}
                {renderStatBlock('Full time results only', stats.today.with_full_time_results_only, stats.all.with_full_time_results_only, 'mdi:clock-warning', 'text-primary')}
                {renderStatBlock('Half & full time results', stats.today.with_half_and_time_results, stats.all.with_half_and_time_results, 'carbon:time-filled', 'text-success')}
                {renderStatBlock('Without results', stats.today.without_results, stats.all.without_results, 'ri:error-warning-line', 'text-danger')}
            </div>
        </div>
    );
};

export default DetailedMatchesInfo