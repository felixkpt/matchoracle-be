import useAutoTableEffect from '@/hooks/useAutoTableEffect';
import { debounce } from 'lodash';
import Pagination from './Pagination';
import { useEffect, useState } from 'react';
import { Icon } from '@iconify/react';
import { useNavigate } from 'react-router-dom';
import { subscribe, unsubscribe } from '@/utils/events';
import { AutoTableInterface } from '../interfaces/UncategorizedInterfaces';
import AutoActions from './AutoActions';
import Str from '@/utils/Str';

// Define the __dangerousHtml function
function __dangerousHtml(html: HTMLElement) {
    // Implement the logic to safely render HTML content here
    return <div dangerouslySetInnerHTML={{ __html: html }} />;
}

const AutoTable = ({ baseUri, listUri, search, columns: initCols, exclude, getModelDetails, list_sources, tableId, modalSize, customModalId, perPage }: AutoTableInterface) => {
    const {
        tableData,
        loading,
        handleOrderBy,
        handleSearch,
        setPage,
        setPerPage,
        setReload,
        hidePerPage,
    } = useAutoTableEffect(baseUri, listUri, { perPage });

    const id = tableId ? tableId : 'AutoTable'

    const [checkedItems, setCheckedItems] = useState<(string | number)[]>([]);
    const [checkedAllItems, setCheckedAllItems] = useState<boolean>(false);
    const [modelDataLength, setModelDataLength] = useState<number>(-1);

    const [modelDetails, setModelDetails] = useState({})
    const [htmls, setHtmls] = useState<string[]>([])

    useEffect(() => {
        if (tableData) {

            if (tableData?.data?.length >= 0) {
                setModelDataLength(tableData.data.length);
            } else {
                setModelDataLength(-1);
            }

            const { data, ...others } = tableData
            if (setModelDetails) {

                const rest = { ...others, tableId: id }

                setModelDetails(rest)
                setHtmls(rest.htmls)
                if (getModelDetails) {
                    getModelDetails(rest)
                }
            }
        } else setModelDataLength(-1);
    }, [tableData]);

    const debouncedSearch = debounce(handleSearch, 400);

    const handleChecked = (checked: boolean, itemId: string | number | null) => {
        if (modelDataLength <= 0) return;

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
        if (modelDataLength <= 0) return;

        if (tableData && checkedItems?.length === tableData.data.length) setCheckedAllItems(true);
        else setCheckedAllItems(false);
    }, [checkedItems]);

    const [columns, setColumns] = useState(initCols)

    const renderTableHeaders = (columns: any) => {
        return columns.map((column: any) => {
            const { label, key, isSorted, sortDirection } = column;

            const handleHeaderClick = () => {
                const newColumns = columns.map((c: any) => ({
                    ...c,
                    isSorted: c.key === key,
                    sortDirection: c.key === key ? (c.sortDirection === 'asc' ? 'desc' : 'asc') : '',
                }));

                handleOrderBy(key);
                setColumns(newColumns);
            };

            return (
                <th key={key} scope='col' className='px-6 py-3 cursor-pointer' onClick={handleHeaderClick}>
                    {Str.title(label || key.split('.')[0])}
                    {isSorted && (
                        <span className='ml-1'>
                            {sortDirection === 'asc' ? (
                                <Icon icon="fluent:caret-up-20-filled" />)
                                : (
                                    <Icon icon="fluent:caret-down-20-filled" />
                                )}
                        </span>
                    )}
                </th>
            );
        });
    };

    useEffect(() => {
        subscribe('reloadAutoTable', reloadAutoTable)

        return () => unsubscribe('reloadAutoTable', reloadAutoTable)
    }, [])

    const reloadAutoTable: EventListener = (event) => {
        setReload((curr) => curr + 1)
    }

    const navigate = useNavigate()

    const autoActions = new AutoActions(modelDetails, tableData, navigate, list_sources, exclude, modalSize, customModalId)

    useEffect(() => {
        if (modelDataLength) {

            const autotableNavigateElements = document.querySelectorAll('.autotable .autotable-navigate');
            autotableNavigateElements.forEach((element) => {
                (element as HTMLElement).addEventListener('click', autoActions.handleNavigation);
            });

            const autotableViewElements = document.querySelectorAll('.autotable .autotable-modal-view');
            autotableViewElements.forEach((element) => {
                (element as HTMLElement).addEventListener('click', autoActions.handleView);
            });

            const autotableModalActionElements = document.querySelectorAll('.autotable [class*="autotable-modal-"]');
            autotableModalActionElements.forEach((element) => {
                (element as HTMLElement).addEventListener('click', autoActions.handleModalAction);
            });

            return () => {
                // Clean up event listeners when the component unmounts
                autotableViewElements.forEach((element) => {
                    (element as HTMLElement).removeEventListener('click', autoActions.handleView);
                });

                autotableNavigateElements.forEach((element) => {
                    (element as HTMLElement).removeEventListener('click', autoActions.handleNavigation);
                });

                autotableModalActionElements.forEach((element) => {
                    (element as HTMLElement).removeEventListener('click', autoActions.handleModalAction);
                });
            };
        }

    }, [navigate, modelDataLength, handleOrderBy]);


    function getDynamicValue(row: any, path: string) {

        if (!path.match(/\./)) {
            const val = row[path]
            return String(val);
        } else {
            return path.split('.').reduce((acc, prop) => acc && acc[prop], row);
        }
    }

    const [countOpacity, setCountOpacity] = useState(0);

    useEffect(() => {
        // Set opacity to 0 when the count changes
        setCountOpacity(0);

        // After a delay, reset opacity to 1
        const opacityTimeout = setTimeout(() => {
            if (tableData?.total)
                setCountOpacity(1);
            if (modelDataLength == 0)
                setCountOpacity(1);
        }, 300);

        // Clean up the timeout to avoid memory leaks
        return () => clearTimeout(opacityTimeout);
    }, [tableData?.total]);

    return (
        <div id={id} className={`autotable shadow p-1 rounded my-3 relative shadow-md sm:rounded-lg`}>
            <div className={`card overflow-auto overflow-x-auto ${modelDataLength >= 0 ? 'overflow-hidden' : 'overflow-auto'}`}>
                <div className="card-header">
                    <div className="d-flex align-items-center justify-content-end"><span className="text-muted autotable-record-counts" style={{ opacity: countOpacity }}>{tableData?.total || 0} {`${tableData?.total == 1 ? 'record' : 'records'}`}</span></div>
                    <div className={`mt-2 h-6 px-3 pb-1 text-sm font-medium leading-none text-center text-blue-800 dark:text-white${modelDataLength >= 0 && loading ? ' animate-pulse' : ''}`}>{modelDataLength >= 0 && loading ? 'Loading...' : ''}</div>
                    <div className="flex items-center justify-between pb-2 px-1.5 float-right gap-2">
                        <label htmlFor="table-search" className="sr-only d-none">Search</label>
                        {
                            search &&
                            <div className="relative">

                                <div className="col-md-12 col-md-offset-3">
                                    <div className="input-group">
                                        <div className="input-group-btn search-panel" data-search="students">
                                            <button type="button" className="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                <span className="search_by">Filter by</span> <span className="caret"></span>
                                            </button>
                                            <ul className="dropdown-menu" role="menu">
                                                <li><a data-search="students">students</a></li>
                                                <li><a data-search="teachers">teachers</a></li>
                                                <li><a data-search="rooms">rooms</a></li>
                                                <li className="divider"></li>
                                                <li><a data-search="all">all</a></li>
                                            </ul>
                                        </div>
                                        <input type="text" className="form-control" name="q" id="search-btn" onChange={(e: any) => debouncedSearch(e.target.value)} placeholder="Search here..." />
                                        <span className="input-group-btn">
                                            <button className="btn btn-default" type="button"><span className="glyphicon glyphicon-search"></span></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        }
                    </div>
                </div>

                <div className="card-body">
                    <table className="table table-hover">
                        <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" className="p-x cursor-default col">
                                    <div className="form-check">
                                        <label className="form-check-label" htmlFor="checkbox-all-search">
                                            <input
                                                id="checkbox-all-search"
                                                className="form-check-input" type="checkbox" value=""
                                                checked={checkedAllItems}
                                                onChange={(e) => handleChecked(e.target.checked, null)} />
                                            All
                                        </label>
                                    </div>

                                </th>
                                {columns && renderTableHeaders(columns)}
                            </tr>
                        </thead>
                        <tbody>
                            {(modelDataLength > 0 && tableData) ? tableData.data.map(row => (
                                <tr key={row.id} className={`"bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600" ${loading === false ? 'opacity-100 transition-opacity duration-1000' : 'opacity-[0.9]'}`}>
                                    <td className="w-4 p-4">
                                        <div className="form-check">
                                            <label className="form-check-label" htmlFor={`checkbox-table-search-${row.id}`}>
                                                <input
                                                    id={`checkbox-table-search-${row.id}`}
                                                    className="form-check-input" type="checkbox"
                                                    onChange={(e) => handleChecked(e.target.checked, row.id)}
                                                    checked={checkedItems.includes(row.id)} />
                                            </label>
                                        </div>
                                    </td>

                                    {columns && columns.map(column => {
                                        return (
                                            <td key={column.key} scope="col" className="px-6 py-3">{(column.key === 'action' || htmls.includes(column.key) === true) ? __dangerousHtml(row[column.key]) : String(getDynamicValue(row, column.key))}</td>
                                        )
                                    })}

                                </tr>
                            ))
                                :
                                (
                                    loading ?
                                        <tr className='opacity-100 transition-opacity duration-1000'>
                                            <td colSpan={(columns?.length || 1) + 2}>
                                                <div className='flex justify-center'>
                                                    <div className="flex items-center justify-center w-full h-40 border border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                                                        <div className="px-3 py-1 text-sm font-medium leading-none text-center text-blue-800 bg-blue-200 rounded-full animate-pulse dark:bg-blue-900 dark:text-blue-200">Loading...</div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        :
                                        <tr className='opacity-100 transition-opacity duration-1000'>
                                            <td colSpan={(columns?.length || 1) + 2}>
                                                <div className='flex justify-center'>
                                                    <div className="flex items-center justify-center w-full h-40 border border-gray-200 bg-gray-50 dark:bg-gray-800 dark:border-gray-700">
                                                        There's nothing here
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                )
                            }

                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                {
                    (modelDataLength >= 0 && tableData) && tableData.per_page &&
                    <Pagination items={tableData} setPage={setPage} setPerPage={setPerPage} hidePerPage={hidePerPage} />
                }
            </div>

        </div>
    )
}

export default AutoTable