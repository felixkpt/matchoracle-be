import Loader from "@/components/Loader";
import NoContentMessage from "@/components/NoContentMessage";
import { CompetitionStatsInterface, TodayCompetitionStatsInterface } from "@/interfaces/FootballInterface";
import { Icon } from "@iconify/react/dist/iconify.js";

interface CompetitionStatsCardProps {
    loading: boolean
    errors: any
    stats: {
        all: CompetitionStatsInterface;
        today: TodayCompetitionStatsInterface;
    } | null | undefined;
}

const Competitions: React.FC<CompetitionStatsCardProps> = ({ loading, errors, stats }) => {

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
        <>
            {
                loading ?
                    <Loader />
                    :
                    <>
                        {
                            errors || !stats ?
                                <NoContentMessage message={errors} />
                                :
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
                                        {renderStatBlock('Seasons Run Counts', stats.today.total_seasons_run_counts, stats.all.total_seasons_run_counts, 'fa-solid:running', 'text-warning')}
                                        {renderStatBlock('Games Run Counts', stats.today.total_games_run_counts, stats.all.total_games_run_counts, 'carbon:checkmark-outline', 'text-success')}
                                    </div>
                                </div>
                        }
                    </>
            }
        </>
    );
};

export default Competitions;
