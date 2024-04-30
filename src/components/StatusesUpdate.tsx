import React, { useEffect, useState } from 'react';
import { Icon } from '@iconify/react';
import Select from 'react-select';
import Str from '@/utils/Str';
import SubmitButton from './SubmitButton';
import { publish } from '@/utils/events';
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';

interface StatusesUpdateProps {
    checkedAllItems: boolean;
    checkedItems: (string | number)[];
    tableDataLength: number; // Adjust the type accordingly
    visibleItemsCounts: number;
    setCheckedAllItems: React.Dispatch<React.SetStateAction<boolean>>;
    moduleUri: string;
    fullQueryString: string;
    statuses: CollectionItemsInterface[]; // Adjust the type accordingly
    tableId: string; // Add tableId prop
}

const StatusesUpdate: React.FC<StatusesUpdateProps> = ({
    checkedAllItems,
    checkedItems,
    tableDataLength,
    visibleItemsCounts,
    setCheckedAllItems,
    moduleUri,
    fullQueryString,
    statuses,
}) => {
    
    const [selectedStatus, setSelectedStatus] = useState<(CollectionItemsInterface)>();

    useEffect(() => {
        if (statuses.length > 0) {
            setSelectedStatus(statuses[0])
        }
    }, [statuses])

    return (
        <div className="d-flex align-items-center justify-content-start gap-3">
            {checkedAllItems ? (
                <div className='d-inline bg-light p-1 rounded'>
                    <Icon icon={`prime:bookmark`} className='me-2' />
                    <span>All {tableDataLength} records selected</span>
                </div>
            ) : checkedItems.length > 0 ? (
                <>
                    {checkedItems.length === visibleItemsCounts && checkedItems?.length !== tableDataLength ? (
                        <div className='d-inline bg-light p-1 rounded'>
                            <Icon icon={`prime:bookmark`} className='me-2' />
                            <span>
                                You have selected {visibleItemsCounts} items,{' '}
                                <span className='text-info cursor-pointer' onClick={() => setCheckedAllItems(true)}>
                                    click here
                                </span>{' '}
                                to include all {tableDataLength} records.
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
                <form key={0} method='post' id='statusesUpdate' data-action={moduleUri + `update-statuses?${fullQueryString}`} onSubmit={(e) => publish('autoPost', e)}>
                    <input type="hidden" name='_method' value='patch' />
                    <input type="hidden" name='ids' value={checkedAllItems ? 'all' : JSON.stringify(checkedItems)} />
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
