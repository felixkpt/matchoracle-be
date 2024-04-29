import React from 'react';
import ReactPaginate from 'react-paginate';
import { Icon } from '@iconify/react/dist/iconify.js';
import Select from 'react-select';
import Loader from './Loader';

interface PaginationProps {
  items: any;
  setPage: (value: string) => void
  setPerPage: (value: string) => void
  setPaginatorChangeKey?: (value: number) => void
  hidePerPage?: boolean
  loading?: boolean
  breakpoint?: 'md' | 'lg' | 'xl'
}

const Pagination: React.FC<PaginationProps> = ({ items, setPage, setPerPage, setPaginatorChangeKey, hidePerPage, loading, breakpoint }) => {
  if (!items) return null;

  const { last_page, per_page } = items;

  const handlePerPageChange = async (e: any) => {

    const value = e?.value || e?.target.value || undefined;
    setPerPage(value)
    if (setPaginatorChangeKey)
      setPaginatorChangeKey((curr) => curr + 1)
  };

  const handlePageClick = (data: any) => {
    const selectedPage = data.selected + 1;
    setPage(selectedPage.toString());
    if (setPaginatorChangeKey)
      setPaginatorChangeKey((curr) => curr + 1)
  };

  const options = [
    { value: 20, label: '20 per page' },
    { value: 50, label: '50 per page' },
    { value: 100, label: '100 per page' },
    { value: 200, label: '200 per page' },
    { value: 500, label: '500 per page' },
    { value: 750, label: '750 per page' },
    { value: 1000, label: '1000 per page' },
  ]

  return (
    <div className='d-flex w-100 flex-column justify-content-center gap-3 mt-5'>
      <div className='col-12'>{loading && <Loader />}</div>
      <div className='col-12'>
        <div className='row justify-content-center align-items-baseline'>
          <nav className={`col-${breakpoint || 'md'}-8 overflow-auto`}>
            <ReactPaginate
              previousLabel={<Icon icon={'mingcute:arrows-left-line'} />}
              nextLabel={<Icon icon={'mingcute:arrows-right-line'} />}
              breakLabel={'...'}
              breakClassName={'px-1'}
              pageCount={last_page}
              marginPagesDisplayed={2}
              pageRangeDisplayed={5}
              onPageChange={handlePageClick}
              containerClassName={'pagination'}
              subContainerClassName={''}
              activeClassName={'active'}
            />
          </nav>
          {
            !hidePerPage
            &&
            <div className={`col-${breakpoint || 'md'}-4`} style={{ maxWidth: '250px' }}>
              <div className="d-flex justify-content-center">
                <Select
                  key={0}
                  className="flatpickr-input"
                  classNamePrefix="select"
                  placeholder="Select per page"
                  defaultValue={per_page ? options.find(v => v.value == per_page) : options[1]}
                  options={options}
                  onChange={(v) => handlePerPageChange(v)}
                />
              </div>
            </div>
          }
        </div>
      </div>
    </div>
  );
};

export default Pagination;
