import { useEffect, useState } from 'react';
import useAutoTableEffect from '@/hooks/useAutoTableEffect';
import { debounce } from 'lodash';
import Pagination from '../Pagination';
import { convertToTitleCase } from '@/utils/helpers';

interface Props {
  baseUri: any
  search: any
  sortColumns: any
}

const AutoTableRecursive = ({ baseUri, search, sortColumns }: Props) => {
  const { tableData, loading, handleOrderBy, handleSearch, setPage, setPerPage } = useAutoTableEffect(
    baseUri
  );

  const [modelDataLength, setModelDataLength] = useState<number>(-1);
  const [searchValue, setSearchValue] = useState('');
  const [columns, setColumns] = useState(sortColumns || []);

  useEffect(() => {
    if (tableData?.data?.length >= 0) {
      setModelDataLength(tableData.data.length);
    } else {
      setModelDataLength(-1);
    }
  }, [tableData]);

  const debouncedSearch = debounce((value) => handleSearch(value), 400);

  useEffect(() => {
    debouncedSearch(searchValue);
    // Cleanup the debounce function on unmount
    return () => {
      debouncedSearch.cancel();
    };
  }, [searchValue]);

  const renderCompetitions = (competitions) => {
    if (!competitions || competitions.length === 0) {
      return null;
    }

    return (
      <ul className='ml-10'>
        {competitions.map((competition) => (
          <li key={competition.id}>{competition.name}</li>
        ))}
      </ul>
    );
  };

  const renderCountries = (countries) => {
    if (!countries || countries.length === 0) {
      return null;
    }

    return (
      <ul>
        {countries.map((country) => (
          <li key={country.id} className='my-4'>
            <div className="flex items-center gap-2 cursor-pointer mb-2">
              <div className="w-8 h-8 bg-white rounded-full inline-block"></div>
              <div className="inline-block text-lg">{country.name}</div>
            </div>
            {renderCompetitions(country.competitions)}
            {renderCountries(country.countries)}
          </li>
        ))}
      </ul>
    );
  };

  const renderTableHeaders = (columns) => {
    return columns.map((column) => {
      const { label, key, isSorted, sortDirection } = column;

      const handleHeaderClick = () => {
        const newColumns = columns.map((c) => ({
          ...c,
          isSorted: c.key === key,
          sortDirection: c.key === key ? (c.sortDirection === 'asc' ? 'desc' : 'asc') : '',
        }));

        handleOrderBy(key);
        setColumns(newColumns);
      };

      return (
        <th key={key} scope='col' className='px-6 py-3 cursor-pointer' onClick={handleHeaderClick}>
          {convertToTitleCase(label)}
          {isSorted && (
            <span className='ml-1'>
              {sortDirection === 'asc' ? (
                <svg
                  xmlns='http://www.w3.org/2000/svg'
                  className='h-4 w-4 inline-block'
                  fill='none'
                  viewBox='0 0 24 24'
                  stroke='currentColor'
                >
                  <path strokeLinecap='round' strokeLinejoin='round' strokeWidth={2} d='M5 15l7-7 7 7' />
                </svg>
              ) : (
                <svg
                  xmlns='http://www.w3.org/2000/svg'
                  className='h-4 w-4 inline-block'
                  fill='none'
                  viewBox='0 0 24 24'
                  stroke='currentColor'
                >
                  <path strokeLinecap='round' strokeLinejoin='round' strokeWidth={2} d='M19 9l-7 7-7-7' />
                </svg>
              )}
            </span>
          )}
        </th>
      );
    });
  };

  return (
    <div className='bg-gray-800 shadow px-2 py-2'>
      <div className='bg-gray-600 px-2 py-3 rounded'>
        <div className='w-full flex gap-4 justify-between'>
          <div className='w-full flex gap-4'>{renderTableHeaders(columns)}</div>
          <div>
            <label htmlFor='table-search' className='sr-only'>
              Search
            </label>
            {search && (
              <div className='relative'>
                <div className='absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none'>
                  <svg
                    className='w-5 h-5 text-gray-500 dark:text-gray-400'
                    aria-hidden='true'
                    fill='currentColor'
                    viewBox='0 0 20 20'
                    xmlns='http://www.w3.org/2000/svg'
                  >
                    <path
                      fillRule='evenodd'
                      d='M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z'
                      clipRule='evenodd'
                    />
                  </svg>
                </div>
                <input
                  onChange={(e: any) => setSearchValue(e.target.value)}
                  type='text'
                  id='table-search-users'
                  className='block p-2 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500'
                  placeholder={`Search ${modelDataLength >= 0 ? tableData.model_name_plural.toLowerCase() : 'here'}...`}
                />
              </div>
            )}
          </div>
        </div>
      </div>

      <div className='my-2'>
        {renderCountries(tableData?.data)}
        {loading && <p>Loading...</p>}
      </div>
      {tableData?.per_page && (
        <Pagination items={tableData} setPage={setPage} setPerPage={setPerPage} />
      )}
    </div>
  );
};

export default AutoTableRecursive;
