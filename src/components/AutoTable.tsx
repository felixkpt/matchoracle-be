import useAutoTableEffect from '@/hooks/useAutoTableEffect';
import useStatusesUpdateEffect from '@/hooks/useStatusesUpdateEffect';
import { debounce } from 'lodash';
import Pagination from './Pagination';
import { useEffect, useState } from 'react';
import { Icon } from '@iconify/react';
import { useNavigate } from 'react-router-dom';
import { subscribe, unsubscribe } from '@/utils/events';
import { AutoTableInterface } from '../interfaces/UncategorizedInterfaces';
import AutoActions from './AutoActions';
import AutoTableHeader from './AutoTableHeader';
import Loader from './Loader';
import StatusesUpdate from './StatusesUpdate';

function __dangerousHtml(html: HTMLElement) {
    return <div dangerouslySetInnerHTML={{ __html: html }} />;
}

const AutoTable = ({ baseUri, search, columns: initCols, exclude, getModelDetails, list_sources, tableId, modalSize, customModalId, perPage }: AutoTableInterface) => {
    const localTableId = tableId ? tableId : 'AutoTable'

    const {
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
        fullQueryString,
    } = useAutoTableEffect(baseUri, localTableId, { perPage });

    const {
        visibleItemsCounts,
        checkedAllItems,
        checkedItems,
        setCheckedItems,
        setCheckedAllItems,
        statuses,
        setPaginatorChangeKey,
        handleChecked,

    } = useStatusesUpdateEffect(tableData, localTableId);

    const [moduleUri, setModuleUri] = useState<(string)>('');

    const [tableDataLength, setTableDataLength] = useState(tableData?.total || 0)
    const [currentPageDataLength, setCurrentPageDataLength] = useState<number>(-1);

    const [modelDetails, setModelDetails] = useState({})
    const [htmls, setHtmls] = useState<string[]>([])
    const [query, setQuery] = useState<string>('')

    useEffect(() => {
        if (tableData) {

            if (tableData?.data?.length >= 0) {
                setTableDataLength(tableData.total);
                setCurrentPageDataLength(tableData.data.length);
                setModuleUri(tableData.module_uri)
            } else {
                setTableDataLength(0);
                setCurrentPageDataLength(-1);
            }

            const { data, ...others } = tableData
            if (setModelDetails) {

                const rest = { ...others, tableId: localTableId, query }

                setModelDetails(rest)
                setHtmls(rest.htmls)
                if (getModelDetails) {
                    getModelDetails(rest)
                }
            }
        } else {
            setTableDataLength(0)
            setCurrentPageDataLength(-1)
        }
    }, [tableData]);

    const debouncedSearch = debounce(handleSearch, 400);
    const debouncedSearch2 = debounce(setQuery, 400);

    const [columns, setColumns] = useState(initCols)

    useEffect(() => {
        subscribe('reloadAutoTable', reloadAutoTable)

        return () => unsubscribe('reloadAutoTable', reloadAutoTable)
    }, [])

    const reloadAutoTable: EventListener | any = (event) => {
        setReload((curr) => curr + 1)
    }

    useEffect(() => {

        subscribe('ajaxPostDone', handleAjaxPostDone as EventListener);

        return () => unsubscribe('ajaxPostDone', handleAjaxPostDone as EventListener);

    }, [])

    const handleAjaxPostDone = (event: CustomEvent<{ [key: string]: any }>) => {

        if (event.detail) {
            const detail = event.detail;

            if (detail.results) {
                if (detail.elementId === 'statusesUpdate') {
                    setReload((curr) => curr + 1)
                    setCheckedItems([])
                }
            }
        }
    }

    const navigate = useNavigate()

    const autoActions = new AutoActions(modelDetails, tableData, navigate, list_sources, exclude, modalSize, customModalId)

    useEffect(() => {
        if (currentPageDataLength) {

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

    }, [navigate, currentPageDataLength, handleOrderBy]);


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
        if (currentPageDataLength == -1) setCountOpacity(0);

        // After a delay, reset opacity to 1
        const opacityTimeout = setTimeout(() => {
            if (tableDataLength)
                setCountOpacity(1);
            if (currentPageDataLength == 0)
                setCountOpacity(1);
        }, 300);

        // Clean up the timeout to avoid memory leaks
        return () => clearTimeout(opacityTimeout);
    }, [tableDataLength]);

    function handleStatus(e: any) {
        const val = e.target.checked
        setStatus(val)
        localStorage.setItem(`app.${localTableId}.status`, JSON.stringify(val))
    }

    return (
        <div id={localTableId} className={`autotable shadow p-1 rounded my-3 relative shadow-md sm:rounded-lg`}>
            <div className={`card`}>
                <div className="card-header">
                    <div className="row align-items-center justify-content-end align-items-center text-muted">
                        <div className='col-12 col-xl-9 cursor-default'>
                            <StatusesUpdate
                                checkedAllItems={checkedAllItems}
                                tableDataLength={tableDataLength}
                                visibleItemsCounts={visibleItemsCounts}
                                setCheckedAllItems={setCheckedAllItems}
                                moduleUri={moduleUri}
                                fullQueryString={fullQueryString}
                                statuses={statuses}
                                checkedItems={checkedItems}
                                tableId={localTableId}
                            />
                        </div>
                        <div className='col-12 col-xl-3'>
                            <div className="d-flex align-items-center justify-content-end gap-1">
                                <div>
                                    <div className='d-flex align-items-center justify-content-end gap-1'>
                                        {!loading && <small title='Click to reload' className='cursor-pointer rounded px-1' onClick={reloadAutoTable}><Icon icon="mdi:reload" /></small>}
                                        <small className="autotable-record-counts" style={{ opacity: countOpacity }}>{tableDataLength.toLocaleString() || 0} {`${tableDataLength == 1 ? 'record' : 'records'}`}</small>
                                    </div>
                                </div>
                                <div className="p-2 align-items-center">
                                    <small className="d-flex gap-1">
                                        <input
                                            className="form-check-input"
                                            id='active_only'
                                            type='checkbox'
                                            name={`active_only`}
                                            defaultChecked={!!status}
                                            onChange={handleStatus}
                                        />
                                        <label className="form-check-label" htmlFor={`active_only`}>
                                            Active only
                                        </label>
                                    </small>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div className={`mt-2 h-6 px-3 pb-1 text-sm font-medium leading-none text-center text-blue-800 dark:text-white${currentPageDataLength >= 0 && loading ? ' animate-pulse' : ''}`}>{currentPageDataLength >= 0 && loading ? <Loader /> : ''}</div>
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
                                        <input type="text" className="form-control" name="q" id="search-btn" onChange={(e: any) => {
                                            debouncedSearch(e.target.value)
                                            debouncedSearch2(e.target.value)
                                        }} placeholder="Search here..." />
                                        <span className="input-group-btn">
                                            <button className="btn btn-default" type="button"><span className="glyphicon glyphicon-search"></span></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        }
                    </div>
                </div>

                <div className={`card-body overflow-auto overflow-x-auto ${currentPageDataLength >= 0 ? 'overflow-hidden' : 'overflow-auto'}`}>
                    <table className="table table-hover">
                        <thead className="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" className="border-top">
                                    <span className='p-x cursor-pointer col'>
                                        <span className="form-check">
                                            <label className="form-check-label" htmlFor="checkbox-all-search">
                                                <input
                                                    id="checkbox-all-search"
                                                    className="form-check-input" type="checkbox" value=""
                                                    checked={checkedItems.length ? checkedItems.length === visibleItemsCounts || checkedItems.length == tableDataLength : false}
                                                    onChange={(e) => handleChecked(e.target.checked, null)} />
                                                <span className='cursor-pointer'>All</span>
                                            </label>
                                        </span>
                                    </span>

                                </th>
                                {columns && <AutoTableHeader columns={columns} handleOrderBy={handleOrderBy} setColumns={setColumns} />}
                            </tr>
                        </thead>
                        <tbody>
                            {(currentPageDataLength > 0 && tableData) ? tableData.data.map(row => (
                                <tr key={row.id} className={`"bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600" ${loading === false ? 'opacity-100 transition-opacity duration-1000' : 'opacity-[0.9]'}`}>
                                    <td className="w-4 p-4">
                                        <div className="form-check">
                                            <label className="form-check-label" htmlFor={`checkbox-table-search-${row.id}`}>
                                                <input
                                                    id={`checkbox-table-search-${row.id}`}
                                                    className="form-check-input checkbox-table-item" type="checkbox"
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
                                                <Loader />
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
                    (currentPageDataLength >= 0 && tableData) && tableData.per_page &&
                    <Pagination items={tableData} setPage={setPage} setPerPage={setPerPage} setPaginatorChangeKey={setPaginatorChangeKey} hidePerPage={hidePerPage} loading={loading} />
                }
            </div>

        </div>
    )
}

export default AutoTable