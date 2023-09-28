import { useEffect, useState } from 'react';
import useAxios from './useAxios';
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';
import { subscribe, unsubscribe } from '@/utils/events';

const useAutoTableEffect = (baseUri: string, listUri: string | undefined) => {
    const [tableData, setTableData] = useState<CollectionItemsInterface>({});
    const [page, setPage] = useState<number | string>(1);
    const [per_page, setPerPage] = useState<number | string>(10);
    const [orderBy, setOrderBy] = useState<string | undefined>(undefined);
    const [orderDirection, setOrderDirection] = useState<string>('asc');
    const [q, setQuery] = useState<string | undefined>(undefined);
    const [reload, setReload] = useState<number>(0);

    // Initialize useAxios with the desired endpoint for fetching the data
    const { data, loading, error, get } = useAxios();

    useEffect(() => {
        fetchData();
    }, [page, per_page, orderBy, orderDirection, q, reload]);

    async function fetchData() {
        try {
            // Fetch data from the API using baseUri and listUri
            // You can customize the request based on your API structure
            // For example, using the Axios instance created in useAxios:
            await get(`${baseUri}${listUri ? '/' + listUri : ''}`.replace(/\/+/, '/'), { params: { page, per_page, q, order_by: orderBy, order_direction: orderDirection } });

        } catch (error) {
            // Handle error if needed
        }
    }

    useEffect(() => {

        // Update the tableData state with the fetched data
        setTableData(data);

    }, [data])

    useEffect(() => {
        // Add event listener for the custom ajaxPost event
        const eventListener = (event: CustomEvent<{ [key: string]: any }>) => {
            const { detail } = event

            if (detail.results || detail.message)
                fetchData()
        };

        subscribe('ajaxPostDone', eventListener as EventListener);

        // Cleanup the event listener when the component unmounts
        return () => {
            unsubscribe('ajaxPostDone', eventListener as EventListener);
        };

    }, []);


    function handleOrderBy(key: string) {
        if (key === orderBy) setOrderDirection((orderDirection) => (orderDirection === 'asc' ? 'desc' : 'asc'));
        setOrderBy(key);
    }

    const handleSearch = (_q: string) => {
        setQuery(_q);
    };

    return {
        tableData,
        loading,
        handleOrderBy,
        handleSearch,
        setPage,
        setPerPage,
        setReload,
    };
};

export default useAutoTableEffect;
