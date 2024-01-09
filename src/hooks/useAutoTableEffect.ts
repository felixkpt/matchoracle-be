import { useEffect, useState } from 'react';
import useAxios from './useAxios';
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';
import { subscribe, unsubscribe } from '@/utils/events';
import Str from '@/utils/Str';

interface AutoTableOptionsInterface {
    perPage: number | undefined
}

const useAutoTableEffect = (baseUri: string, listUri: string | undefined, options: AutoTableOptionsInterface) => {
    const [tableData, setTableData] = useState<CollectionItemsInterface | null>(null);
    const [page, setPage] = useState<number | string>(1);
    const [per_page, setPerPage] = useState<number | string>(options.perPage || 50);
    const [orderBy, setOrderBy] = useState<string | undefined>(undefined);
    const [orderDirection, setOrderDirection] = useState<string>('desc');
    const [q, setQuery] = useState<string | undefined>(undefined);
    const [reload, setReload] = useState<number>(0);
    const [hidePerPage, setHidePerPage] = useState<boolean>(false);

    // Initialize useAxios with the desired endpoint for fetching the data
    const { data, loading, error, get } = useAxios();

    useEffect(() => {
        fetchData();
    }, [page, per_page, orderBy, orderDirection, q, reload]);

    async function fetchData() {
        try {

            const params: { [key: string]: any } = {};

            const url = `${baseUri}${listUri ? '/' + listUri : ''}`;

            if (!Str.contains(url, 'page')) {
                params.page = page
            }
            if (!Str.contains(url, 'per_page')) {
                params.per_page = per_page
            }
            if (!Str.contains(url, 'order_by')) {
                params.order_by = orderBy
            }
            if (!Str.contains(url, 'order_direction')) {
                params.order_direction = orderDirection
            }
            if (!Str.contains(url, 'q')) {
                params.q = q
            }

            // Check if the URL contains 'hide_per_page'
            if (Str.contains(url, 'hide_per_page'))
                setHidePerPage(true)

            // Fetch data from the API using baseUri and listUri
            await get(url.replace(/\/+/, '/'), { params: params });

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
        hidePerPage,
    };
};

export default useAutoTableEffect;
