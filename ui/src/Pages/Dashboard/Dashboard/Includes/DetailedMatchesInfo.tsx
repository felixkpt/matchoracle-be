import Loader from "@/components/Loader";
import NoContentMessage from "@/components/NoContentMessage";
import { MatchesInterface, CustomMatchesInterface } from "@/interfaces/FootballInterface";
import RenderStatBlock from "./RenderStatBlock";

interface MatchesCardProps {
    loading: boolean;
    errors: string | undefined;
    stats: {
        custom: CustomMatchesInterface;
        all: MatchesInterface;
    } | null | undefined;
}

const DetailedMatchesInfo: React.FC<MatchesCardProps> = ({ loading, errors, stats }) => {
    // Default stats object with zeros
    const defaultStats = {
        custom: {
            totals: 0,
            past: 0,
            upcoming: 0,
            with_full_time_results_only: 0,
            with_half_and_time_results: 0,
            without_results: 0,
        },
        all: {
            totals: 0,
            past: 0,
            upcoming: 0,
            with_full_time_results_only: 0,
            with_half_and_time_results: 0,
            without_results: 0,
        },
    };

    const statsData = stats ? stats : defaultStats;

    return (
        <>
            {loading ? (
                <Loader />
            ) : (
                <>
                    {errors ? (
                        <NoContentMessage message={errors} />
                    ) : (
                        <div className='d-flex align-items-center justify-content-between shadow-sm px-1 py-2 mb-2 rounded bg-light'>
                            <div className='d-flex align-items-center gap-2'>
                                <h6 className='text-muted'>Description</h6>
                            </div>
                            <div className='d-flex w-50 justify-content-around'>
                                <h6 className='text-muted'>Today</h6>
                                <h6 className='text-muted'>All Time</h6>
                            </div>
                        </div>
                    )}
                    <div className="d-flex flex-column gap-2 mt-3">
                        <RenderStatBlock
                            label="Matches"
                            customCount={statsData.custom.totals}
                            allTimeCount={statsData.all.totals}
                            icon="ic:sharp-published-with-changes"
                            colorClass="text-success"
                        />
                        <RenderStatBlock
                            label="Played"
                            customCount={statsData.custom.past}
                            allTimeCount={statsData.all.past}
                            icon="mdi:trophy"
                            colorClass="text-info"
                        />
                        <RenderStatBlock
                            label="Fixtures"
                            customCount={statsData.custom.upcoming}
                            allTimeCount={statsData.all.upcoming}
                            icon="fa-solid:running"
                            colorClass="text-warning"
                        />
                        <RenderStatBlock
                            label="Full time results only"
                            customCount={statsData.custom.with_full_time_results_only}
                            allTimeCount={statsData.all.with_full_time_results_only}
                            icon="mdi:clock-warning"
                            colorClass="text-primary"
                        />
                        <RenderStatBlock
                            label="Half & full time results"
                            customCount={statsData.custom.with_half_and_time_results}
                            allTimeCount={statsData.all.with_half_and_time_results}
                            icon="carbon:time-filled"
                            colorClass="text-success"
                        />
                        <RenderStatBlock
                            label="Without results"
                            customCount={statsData.custom.without_results}
                            allTimeCount={statsData.all.without_results}
                            icon="ri:error-warning-line"
                            colorClass="text-danger"
                        />
                    </div>
                </>
            )}
        </>
    );
};

export default DetailedMatchesInfo;
