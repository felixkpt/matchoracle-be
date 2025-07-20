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
            <div className="row align-items-center justify-content-between">
                <div className='col-sm-12 shadow-sm rounded text-success'>
                    <div className="d-flex justify-content-between align-items-center gap-2">
                        <span className='d-flex align-items-center gap-1'>
                            <Icon width={'1rem'} icon={`${'ic:sharp-published-with-changes'}`} />
                            Active:
                        </span>
                        <span>{active}</span>
                    </div>
                </div>
                <div className='col-sm-12 shadow-sm rounded text-danger'>
                    <div className="d-flex justify-content-between align-items-center gap-2">
                        <span className='d-flex align-items-center gap-1'>
                            <Icon width={'1rem'} icon={`${'fe:disabled'}`} />
                            In-active:
                        </span>
                        <span>{inactive}</span>
                    </div>
                </div>
            </div>
        </>
    );
};

export default DashMiniCard;
