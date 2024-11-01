import React from 'react';
import { DashJobLogsInterface } from '@/interfaces/FootballInterface';
import NoContentMessage from '@/components/NoContentMessage';
import Loader from '@/components/Loader';
import RenderStatBlock from '../Includes/RenderStatBlock';
import Str from '@/utils/Str';

interface DashJobLogsMiniCardProps {
    loading: boolean;
    errors: string | undefined;
    stats: {
        today: DashJobLogsInterface;
        all: DashJobLogsInterface;
    } | null;
}

const DashMatchJobLogsCard: React.FC<DashJobLogsMiniCardProps> = ({
    loading,
    errors,
    stats,
}) => {

    // Function to initialize stats data
    const initializeStatsData = (): DashJobLogsInterface => ({
        total_job_run_counts: 0,
        total_competition_counts: 0,
        total_run_competition_counts: 0,
        total_action_counts: 0,
        total_run_action_counts: 0,
        total_average_seconds_per_action: 0,
        total_created_counts: 0,
        total_updated_counts: 0,
        total_failed_counts: 0,
        remaining_time: 0,
    });

    const statsData = {
        today: stats?.today ? { ...initializeStatsData(), ...stats.today } : initializeStatsData(),
        all: stats?.all ? { ...initializeStatsData(), ...stats.all } : initializeStatsData(),
    };

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
                    <div className='d-flex flex-column gap-2 w-100'>
                        <RenderStatBlock
                            icon='ic:sharp-published-with-changes'
                            label='Job Runs'
                            todayCount={statsData.today.total_job_run_counts}
                            allTimeCount={statsData.all.total_job_run_counts}
                            colorClass='text-success'
                        />
                        <RenderStatBlock
                            icon='mdi:trophy'
                            label='Competitions Done/Counts'
                            todayCount={`${statsData.today.total_run_competition_counts}/${statsData.today.total_competition_counts}`}
                            allTimeCount={`${statsData.all.total_run_competition_counts}/${statsData.all.total_competition_counts}`}
                            colorClass='text-info'
                        />
                        <RenderStatBlock
                            icon='fa-solid:running'
                            label='Actions Done/Counts'
                            todayCount={`${statsData.today.total_run_action_counts}/${statsData.today.total_action_counts}`}
                            allTimeCount={`${statsData.all.total_run_action_counts}/${statsData.all.total_action_counts}`}
                            colorClass='text-primary'
                        />
                        <RenderStatBlock
                            icon='fa-solid:running'
                            label='AVG Time / Action'
                            todayCount={statsData.today.total_average_seconds_per_action}
                            allTimeCount={statsData.all.total_average_seconds_per_action}
                            colorClass='text-primary'
                        />
                        <RenderStatBlock
                            icon='carbon:checkmark-outline'
                            label='Created'
                            todayCount={statsData.today.total_created_counts}
                            allTimeCount={statsData.all.total_created_counts}
                            colorClass='text-success'
                        />
                        <RenderStatBlock
                            icon='carbon:checkmark-outline'
                            label='Updated'
                            todayCount={statsData.today.total_updated_counts}
                            allTimeCount={statsData.all.total_updated_counts}
                            colorClass='text-warning'
                        />
                        <RenderStatBlock
                            icon='ri:error-warning-line'
                            label='Failures'
                            todayCount={statsData.today.total_failed_counts}
                            allTimeCount={statsData.all.total_failed_counts}
                            colorClass='text-danger'
                        />
                        {/* Estimated Remaining Time Block */}
                        <RenderStatBlock
                            icon='mdi:timer-sand'
                            label='Remaining Time'
                            todayCount={Str.formatTime(statsData.today.remaining_time || 0)}
                            allTimeCount={Str.formatTime(statsData.all.remaining_time || 0)}
                            colorClass='text-secondary'
                        />
                    </div>
                </>
            )}
        </>
    );
};

export default DashMatchJobLogsCard;
