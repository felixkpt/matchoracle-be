import React from 'react';
import { Icon } from '@iconify/react/dist/iconify.js';

interface DashMiniCardProps {
    total: number;
    active: number;
    inactive: number;
}

const DashMiniCard: React.FC<DashMiniCardProps> = ({ total, active, inactive }) => {
    return (
        <>
            <div className='mb-3'>
                <span className="shadow-sm p-2 rounded text-muted fs-5">Total: {total}</span>
            </div>
            <div className="d-flex align-items-center gap-1 justify-content-between">
                <div className='d-flex align-items-center gap-2 shadow-sm p-2 rounded text-success'>
                    <span className='d-flex align-items-center gap-1'>
                        <Icon width={'1rem'} icon={`${'ic:sharp-published-with-changes'}`} />
                        Active:
                    </span>
                    {active}
                </div>
                <div className='d-flex align-items-center gap-2 shadow-sm p-2 rounded text-danger'>
                    <span className='d-flex align-items-center gap-1'>
                        <Icon width={'1rem'} icon={`${'fe:disabled'}`} />
                        In-active:
                    </span>
                    {inactive}
                </div>
            </div>
        </>
    );
};

export default DashMiniCard;
