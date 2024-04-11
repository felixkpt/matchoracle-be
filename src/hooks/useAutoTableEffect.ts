import { useEffect, useState } from 'react';
import useAxios from './useAxios';
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';
import queryString from 'query-string';

interface AutoTableOptionsInterface {
    perPage: number | undefined
}

const useAutoTableEffect = (baseUri: string, listUri: string | undefined, tableId: string | undefined, options: AutoTableOptionsInterface) => {
    const [tableData, setTableData] = useState<CollectionItemsInterface | null>(null);
    const [page, setPage] = useState<number | string>(1);
    const [per_page, setPerPage] = useState<number | string>(options.perPage || 50);
    const [orderBy, setOrderBy] = useState<string | undefined>(undefined);
    const [orderDirection, setOrderDirection] = useState<string>('desc');
    const [q, setQuery] = useState<string | undefined>(undefined);
    const [reload, setReload] = useState<number>(0);
    const [hidePerPage, setHidePerPage] = useState<boolean>(false);
    const [params, setParams] = useState<{ [key: string]: string | undefined }>({});
    const [url, setUrl] = useState<string>(`${baseUri}${listUri ? '/' + listUri : ''}`);
    const [status, setStatus] = useState<boolean>(() => {
        const stored_state = localStorage.getItem(`app.${tableId}.status`)
        let show = false
        if (stored_state)
            show = JSON.parse(stored_state)

        return show
    })

    // Initialize useAxios with the desired endpoint for fetching the data
    const { data, loading, error, get } = useAxios();

    useEffect(() => {
        fetchData();
    }, [page, per_page, orderBy, orderDirection, q, reload, status]);

    async function fetchData() {
        try {

            const mergedParams = { ...params };
            mergedParams['q'] = q
            mergedParams['status'] = status ? 1 : 0
            mergedParams['page'] = page
            mergedParams['per_page'] = per_page
            mergedParams['order_by'] = orderBy
            mergedParams['order_direction'] = orderDirection

            const parsedUrlParams = queryString.parseUrl(url).query;
            const newUrl = queryString.parseUrl(url).url

            // Merge parameters from the provided URL with the hook-managed parameters
            Object.keys(parsedUrlParams).forEach(key => {
                if (!(key in mergedParams)) {
                    mergedParams[key] = parsedUrlParams[key];
                }
            });

            const queryStringParams = queryString.stringify(mergedParams);
            const finalUrl = `${newUrl}?${queryStringParams}`;

            // Check if the URL contains 'hide_per_page'
            if (parsedUrlParams?.hide_per_page) {
                setHidePerPage(true);
            }

            // Fetch data from the API using baseUri and listUri
            await get(finalUrl.replace(/\/+/, '/'));
        } catch (error) {
            // Handle error if needed
        }
    }

    useEffect(() => {
        // Update the tableData state with the fetched data
        setTableData(data);
    }, [data])

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
        status,
        setStatus,
    };
};

export default useAutoTableEffect;
