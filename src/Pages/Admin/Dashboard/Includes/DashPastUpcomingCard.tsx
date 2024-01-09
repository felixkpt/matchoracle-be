import React from 'react';
import { Icon } from '@iconify/react/dist/iconify.js';

interface DashMiniCardProps {
    total: number;
    past: number;
    upcoming: number;
}

const DashPastUpcomingCard: React.FC<DashMiniCardProps> = ({ total, past, upcoming }) => {
    return (
        <>
            <div className='mb-3'>
                <span className="shadow-sm p-2 rounded text-muted fs-5">Total: {total}</span>
            </div>
            <div className="d-flex align-items-center gap-1 justify-content-between">
                <div className='d-flex align-items-center gap-2 shadow-sm p-2 rounded text-success'>
                    <span className='d-flex align-items-center gap-1'>
                        <Icon width={'1rem'} icon={`${'ic:sharp-published-with-changes'}`} />
                        Past:
                    </span>
                    {past}
                </div>
                <div className='d-flex align-items-center gap-2 shadow-sm p-2 rounded text-info'>
                    <span className='d-flex align-items-center gap-1'>
                        <Icon width={'1rem'} icon={`${'fe:disabled'}`} />
                        Upcoming:
                    </span>
                    {upcoming}
                </div>
            </div>
        </>
    );
};

export default DashPastUpcomingCard;
