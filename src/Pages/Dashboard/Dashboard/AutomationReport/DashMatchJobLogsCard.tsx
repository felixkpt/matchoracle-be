import React from 'react';
import { Icon } from '@iconify/react/dist/iconify.js';
import { DashJobLogsInterface } from '@/interfaces/FootballInterface';
import NoContentMessage from '@/components/NoContentMessage';
import Loader from '@/components/Loader';

interface DashJobLogsMiniCardProps {
    loading: boolean
    errors: any
    stats: {
        'today': DashJobLogsInterface;
        'all': DashJobLogsInterface;
    } | null
    jobMessage?: string
    jobActionMessage?: string
}

const DashMatchJobLogsCard: React.FC<DashJobLogsMiniCardProps> = ({ loading, errors, stats, jobMessage, jobActionMessage }) => {

    let today_total_job_run_counts = 0
    let total_job_run_counts = 0
    let today_total_competition_run_counts = 0
    let total_competition_run_counts = 0
    let today_total_fetch_run_counts = 0
    let total_fetch_run_counts = 0
    let today_total_fetch_success_counts = 0
    let total_fetch_success_counts = 0
    let today_total_fetch_failed_counts = 0
    let total_fetch_failed_counts = 0
    let today_total_updated_items_counts = 0
    let total_updated_items_counts = 0

    if (stats) {
        today_total_job_run_counts = stats.today.total_job_run_counts || 0;
        total_job_run_counts = stats.all.total_job_run_counts || 0;
        today_total_competition_run_counts = stats.today.total_competition_run_counts || 0;
        total_competition_run_counts = stats.all.total_competition_run_counts || 0;
        today_total_fetch_run_counts = stats.today.total_fetch_run_counts || 0;
        total_fetch_run_counts = stats.all.total_fetch_run_counts || 0;
        today_total_fetch_success_counts = stats.today.total_fetch_success_counts || 0;
        total_fetch_success_counts = stats.all.total_fetch_success_counts || 0;
        today_total_fetch_failed_counts = stats.today.total_fetch_failed_counts || 0;
        total_fetch_failed_counts = stats.all.total_fetch_failed_counts || 0;
        today_total_updated_items_counts = stats.today.total_updated_items_counts || 0;
        total_updated_items_counts = stats.all.total_updated_items_counts || 0;
    }


    return (
        <>
            {
                loading ?
                    <Loader />
                    :
                    <>
                        {
                            errors && stats ?
                                <NoContentMessage isError={errors} />
                                :
                                <>
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
                                        <div className='d-flex align-items-center justify-content-between shadow-sm p-2 rounded text-success'>
                                            <div className='d-flex align-items-center gap-1 col-6'>
                                                <Icon width={'1rem'} icon={`${'ic:sharp-published-with-changes'}`} />
                                                Total Jobs Runs:
                                            </div>
                                            <div className="row col-6">
                                                <div className="col-6">{today_total_job_run_counts}</div>
                                                <div className="col-6">{total_job_run_counts}</div>
                                            </div>
                                        </div>
                                        <div className='d-flex align-items-center justify-content-between shadow-sm p-2 rounded text-info'>
                                            <div className='d-flex align-items-center gap-1 col-6'>
                                                <Icon width={'1rem'} icon={`${'mdi:trophy'}`} />
                                                Competition Runs:
                                            </div>
                                            <div className="row col-6">
                                                <div className="col-6">{today_total_competition_run_counts}</div>
                                                <div className="col-6">{total_competition_run_counts}</div>
                                            </div>
                                        </div>
                                        <div className='d-flex align-items-center justify-content-between shadow-sm p-2 rounded text-warning'>
                                            <div className='d-flex align-items-center gap-1 col-6'>
                                                <Icon width={'1rem'} icon={`${'fa-solid:running'}`} />
                                                {jobActionMessage || 'Fetch'} Runs:
                                            </div>
                                            <div className="row col-6">
                                                <div className="col-6">{today_total_fetch_run_counts}</div>
                                                <div className="col-6">{total_fetch_run_counts}</div>
                                            </div>
                                        </div>
                                        <div className='d-flex align-items-center justify-content-between shadow-sm p-2 rounded text-success'>
                                            <div className='d-flex align-items-center gap-1 col-6'>
                                                <Icon width={'1rem'} icon={`${'carbon:checkmark-outline'}`} />
                                                {jobActionMessage || 'Fetch'} Successes:
                                            </div>
                                            <div className="row col-6">
                                                <div className="col-6">{today_total_fetch_success_counts}</div>
                                                <div className="col-6">{total_fetch_success_counts}</div>
                                            </div>
                                        </div>
                                        <div className='d-flex align-items-center justify-content-between shadow-sm p-2 rounded text-danger'>
                                            <div className='d-flex align-items-center gap-1 col-6'>
                                                <Icon width={'1rem'} icon={`${'ri:error-warning-line'}`} />
                                                {jobActionMessage || 'Fetch'} Failures:
                                            </div>
                                            <div className="row col-6">
                                                <div className="col-6">{today_total_fetch_failed_counts}</div>
                                                <div className="col-6">{total_fetch_failed_counts}</div>
                                            </div>
                                        </div>
                                        <div className='d-flex align-items-center justify-content-between shadow-sm p-2 rounded text-primary'>
                                            <div className='d-flex align-items-center gap-1 col-6'>
                                                <Icon width={'1rem'} icon={`${'bx:bxs-calendar-event'}`} />
                                                Updated {jobMessage || 'Items'}:
                                            </div>
                                            <div className="row col-6">
                                                <div className="col-6">{today_total_updated_items_counts}</div>
                                                <div className="col-6">{total_updated_items_counts}</div>
                                            </div>
                                        </div>
                                    </div>
                                </>
                        }
                    </>
            }
        </>
    );
};

export default DashMatchJobLogsCard;
