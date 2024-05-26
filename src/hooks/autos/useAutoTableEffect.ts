import { useEffect, useState } from 'react';
import useAxios from '../useAxios';
import { CollectionItemsInterface } from '@/interfaces/UncategorizedInterfaces';
import queryString, { ParsedQuery } from 'query-string';
import { useParams } from 'react-router-dom';
import { config } from '@/utils/helpers';

interface AutoTableOptionsInterface {
    perPage?: string | undefined;
}

const useAutoTableEffect = (
    baseUri: string,
    tableId: string | undefined,
    options: AutoTableOptionsInterface
) => {
    const [tableData, setTableData] = useState<CollectionItemsInterface | null>(null);
    const [page, setPage] = useState<string | undefined>('1');
    const [per_page, setPerPage] = useState<string | undefined>(options.perPage || '50');
    const [orderBy, setOrderBy] = useState<string | undefined>(undefined);
    const [orderDirection, setOrderDirection] = useState<string>('desc');
    const [searchTerm, setSearchTerm] = useState<string | undefined>(undefined);
    const [reload, setReload] = useState<number>(0);
    const [hidePerPage, setHidePerPage] = useState<boolean>(false);
    const [fullQueryString, setFullQueryString] = useState<string>(baseUri);
    const { id } = useParams<string>();

    const [status, setStatus] = useState<number>(() => {
        const stored_state = localStorage.getItem(`${config.storageName}.${tableId}.status`);
        let show = 0; // default to 0 (false)
        if (stored_state) {
            show = JSON.parse(stored_state) ? 1 : 0;
        }
        return show;
    });

    // Initialize useAxios with the desired endpoint for fetching the data
    const { response, loading, get } = useAxios();

    useEffect(() => {
        fetchData();
    }, [page, per_page, orderBy, orderDirection, searchTerm, reload, status]);

    async function fetchData() {
        try {
            const mergedParams = <{ [key: string]: string | undefined }>{};
            mergedParams['id'] = id || '';
            mergedParams['search'] = searchTerm;
            mergedParams['status'] = status ? '1' : '0';
            mergedParams['page'] = page;
            mergedParams['per_page'] = per_page;
            mergedParams['order_by'] = orderBy;
            mergedParams['order_direction'] = orderDirection;

            // Cast parsedUrlParams to ParsedQuery<string | undefined>
            const parsedUrlParams = queryString.parseUrl(baseUri).query as ParsedQuery<string | undefined>;

            // Ensure parsedUrlParams is of type { [key: string]: string | undefined }
            const queryParams: { [key: string]: string | undefined } = parsedUrlParams ? parsedUrlParams : {};

            const newUrl = queryString.parseUrl(baseUri).url;

            // Merge parameters from the provided URL with the hook-managed parameters
            Object.keys(queryParams).forEach((key) => {
                if (!(key in mergedParams)) {
                    mergedParams[key] = queryParams[key];
                }
            });

            const queryStringParams = queryString.stringify(mergedParams);
            setFullQueryString(queryStringParams)

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
        setTableData(response.results);
    }, [response]);

    function handleOrderBy(key: string) {
        if (key === orderBy) setOrderDirection((orderDirection) => (orderDirection === 'asc' ? 'desc' : 'asc'));
        setOrderBy(key);
    }

    const handleSearch = (_query: string) => {
        setSearchTerm(_query);
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
        searchTerm,
        setSearchTerm,
        fullQueryString,
    };
};

export default useAutoTableEffect;
