import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';
import { useEffect, useState } from 'react';

const useStatusesUpdateEffect = (
    tableData: CollectionItemsInterface | null,
    tableId: string | undefined,
) => {

    const [currentPageDataLength, setCurrentPageDataLength] = useState<number>(-1);
    const [statuses, setStatuses] = useState<(string | number)[]>([]);
   
    useEffect(() => {
        if (tableData) {

            if (tableData?.data?.length >= 0) {
                setCurrentPageDataLength(tableData.data.length);
                setStatuses(tableData.statuses)
            } else {
                setCurrentPageDataLength(-1);
            }

        } else setCurrentPageDataLength(-1);
    }, [tableData]);

    const [visibleItemsCounts, setVisibleItemsCounts] = useState<number>(0);
    const [checkedAllItems, setCheckedAllItems] = useState<boolean>(false);
    const [checkedItems, setCheckedItems] = useState<(string | number)[]>([]);
    const [paginatorChangeKey, setPaginatorChangeKey] = useState<number>(0)


    useEffect(() => {

        if (currentPageDataLength <= 0 || !tableData) return;

        const timed = setTimeout(() => {
            const checkboxTableItems = document.querySelectorAll(`#${tableId} .checkbox-table-item`)?.length
            if (checkboxTableItems) {
                setVisibleItemsCounts(checkboxTableItems)
                if (checkedItems?.length !== checkboxTableItems) setCheckedAllItems(false);
            }
        }, 1000);

        return () => clearTimeout(timed)

    }, [checkedItems, currentPageDataLength]);

    const handleChecked = (checked: boolean, itemId: string | number | null) => {
        if (currentPageDataLength <= 0) return;

        if (itemId !== null) {
            if (checked) {
                // Add the item ID to checkedItems
                setCheckedItems((prev) => [...prev, itemId]);
            } else {
                // Remove the item ID from checkedItems
                setCheckedItems((prevCheckedItems) =>
                    prevCheckedItems.filter((id) => id !== itemId)
                );
            }
        } else {
            if (checked) {
                // Check all items
                const allIds = tableData ? tableData.data.map((row) => row.id) : [];
                setCheckedItems(allIds);
            } else {
                // Uncheck all items
                setCheckedItems([]);
            }
        }
    };

    useEffect(() => {
        if (paginatorChangeKey > 0 && checkedItems.length > 0) setCheckedItems([])
    }, [paginatorChangeKey])

    return {
        visibleItemsCounts,
        checkedAllItems,
        checkedItems,
        setCheckedItems,
        setCheckedAllItems,
        statuses,
        setPaginatorChangeKey,
        handleChecked,
    };
};

export default useStatusesUpdateEffect;
