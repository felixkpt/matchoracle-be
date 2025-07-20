import Loader from "@/components/Loader";
import NoContentMessage from "@/components/NoContentMessage";
import { CompetitionStatsInterface, CustomCompetitionStatsInterface } from "@/interfaces/FootballInterface";
import RenderStatBlock from "../RenderStatBlock";

interface CompetitionStatsCardProps {
    loading: boolean
    errors: string | undefined
    stats: {
        all: CompetitionStatsInterface;
        custom: CustomCompetitionStatsInterface;
    } | null | undefined;
}

const Competitions: React.FC<CompetitionStatsCardProps> = ({ loading, errors, stats }) => {


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
                                    <div className='d-flex align-items-center justify-content-between shadow-sm p-2 mb-2 rounded bg-light'>
                                        <div className='d-flex align-items-center gap-1 col-6'>
                                            <h6>Description</h6>
                                        </div>
                                        <div className="row col-6">
                                            <h6 className='col-6'>Today</h6>
                                            <h6 className='col-6'>All time</h6>
                                        </div>
                                    </div>
                                    <div className="d-flex flex-column gap-2 mt-3">
                                        <RenderStatBlock
                                            label="Job Run Count"
                                            customCount={stats.custom.total_job_run_count}
                                            allTimeCount={stats.all.total_job_run_count}
                                            icon="ic:sharp-published-with-changes"
                                            colorClass="text-success"
                                        />
                                        <RenderStatBlock
                                            label="Competition Counts"
                                            customCount={stats.custom.total_competition_counts}
                                            allTimeCount={stats.all.total_competition_counts}
                                            icon="mdi:trophy"
                                            colorClass="text-info"
                                        />
                                        <RenderStatBlock
                                            label="Completed Competition Counts"
                                            customCount={stats.custom.total_run_competition_counts}
                                            allTimeCount={stats.all.total_run_competition_counts}
                                            icon="mdi:trophy"
                                            colorClass="text-info"
                                        />
                                        <RenderStatBlock
                                            label="Seasons Run Counts"
                                            customCount={stats.custom.total_seasons_run_counts}
                                            allTimeCount={stats.all.total_seasons_run_counts}
                                            icon="fa-solid:running"
                                            colorClass="text-warning"
                                        />
                                        <RenderStatBlock
                                            label="Games Run Counts"
                                            customCount={stats.custom.total_games_run_counts}
                                            allTimeCount={stats.all.total_games_run_counts}
                                            icon="carbon:checkmark-outline"
                                            colorClass="text-success"
                                        />
                                    </div>
                                </div>
                        }
                    </>
            }
        </>
    );
};

export default Competitions;
