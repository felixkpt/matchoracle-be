import { BettingTipsStatsInterface } from "@/interfaces/FootballInterface";
import { Icon } from "@iconify/react/dist/iconify.js";

interface CompetitionStatsCardProps {
    stats:
    {
        all: BettingTipsStatsInterface
        today: BettingTipsStatsInterface
    }

}

const BettingTips: React.FC<CompetitionStatsCardProps> = ({ stats }) => {

    const renderStatBlock = (label: string, today: number, allTime: number, icon: string, colorClass: string) => (
        <div className={`d-flex align-items-center justify-content-between shadow-sm p-2 rounded ${colorClass}`}>
            <div className='d-flex align-items-center gap-1 col-6'>
                <Icon width={'1rem'} icon={icon} />
                {label}:
            </div>
            <div className="row col-6">
                <div className="col-6">{today || 0}</div>
                <div className="col-6">{allTime || 0}</div>
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
                {renderStatBlock('Job Run Count', stats.today.total_job_run_count, stats.all.total_job_run_count, 'ic:sharp-published-with-changes', 'text-success')}
                {renderStatBlock('Competition Run Counts', stats.today.total_competition_run_counts, stats.all.total_competition_run_counts, 'mdi:trophy', 'text-info')}
                {renderStatBlock('Types Run Counts', stats.today.total_types_run_counts, stats.all.total_types_run_counts, 'mdi:trophy', 'text-warning')}
                {renderStatBlock('Games Run Counts', stats.today.total_games_run_counts, stats.all.total_games_run_counts, 'carbon:checkmark-outline', 'text-success')}
            </div>
        </div>
    );
};

export default BettingTips;
