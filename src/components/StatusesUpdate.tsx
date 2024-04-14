import React from 'react';
import { Icon } from '@iconify/react';
import Select from 'react-select';
import Str from '@/utils/Str';
import SubmitButton from './SubmitButton';
import { publish } from '@/utils/events';

interface StatusesUpdateProps {
    checkedAllItems: boolean;
    checkedItems: (string | number)[];
    tableData: any; // Adjust the type accordingly
    visibleItemsCounts: number;
    setCheckedAllItems: React.Dispatch<React.SetStateAction<boolean>>;
    moduleUri: string;
    fullQueryString: string;
    statuses: any[]; // Adjust the type accordingly
    selectedStatus: any; // Adjust the type accordingly
    setSelectedStatus: React.Dispatch<React.SetStateAction<any>>; // Adjust the type accordingly
    setStatus: React.Dispatch<React.SetStateAction<any>>; // Add setStatus prop
    localTableId: string; // Add localTableId prop
}

const StatusesUpdate: React.FC<StatusesUpdateProps> = ({
    checkedAllItems,
    checkedItems,
    tableData,
    visibleItemsCounts,
    setCheckedAllItems,
    moduleUri,
    fullQueryString,
    statuses,
    selectedStatus,
    setSelectedStatus,
    setStatus,
    localTableId,
}) => {
    
    return (
        <div className="d-flex align-items-center justify-content-start gap-3">
            {checkedAllItems ? (
                <div className='d-inline bg-light p-1 rounded'>
                    <Icon icon={`prime:bookmark`} className='me-2' />
                    <span>All {tableData?.total} records selected</span>
                </div>
            ) : checkedItems.length > 0 ? (
                <>
                    {checkedItems.length === visibleItemsCounts && checkedItems?.length !== tableData?.total ? (
                        <div className='d-inline bg-light p-1 rounded'>
                            <Icon icon={`prime:bookmark`} className='me-2' />
                            <span>
                                You have selected {visibleItemsCounts} items,{' '}
                                <span className='text-info cursor-pointer' onClick={() => setCheckedAllItems(true)}>
                                    click here
                                </span>{' '}
                                to include all {tableData?.total} records.
                            </span>
                        </div>
                    ) : (
                        <div className='d-inline bg-light p-1 rounded'>
                            <Icon icon={`prime:bookmark`} className='me-2' />
                            <span>{checkedItems.length} records selected</span>
                        </div>
                    )}
                </>
            ) : null}
            {checkedItems.length > 0 && (
                <form key={0} method='post' id='statusesUpdate' action-url={moduleUri + `update-status?${fullQueryString}`} onSubmit={(e) => publish('ajaxPost', e)}>
                    <input type="hidden" name='_method' value='patch' />
                    <input type="hidden" name='ids' value={checkedAllItems ? 'all' : checkedItems} />
                    <div style={{ minWidth: '160px' }} className='d-flex align-items-center gap-2'>
                        Status update:
                        <Select
                            name='status_id'
                            options={statuses}
                            value={selectedStatus}
                            onChange={setSelectedStatus}
                            getOptionValue={(option: any) => `${option['id']}`}
                            getOptionLabel={(option: any) => Str.title(`${option['name']}`)}
                        />
                        <SubmitButton />
                    </div>
                </form>
            )}
        </div>
    );
};

export default StatusesUpdate;
