import React from 'react';
import ReactPaginate from 'react-paginate';
import { Icon } from '@iconify/react/dist/iconify.js';
import Select from 'react-select';

interface PaginationProps {
  items: any;
  setPage: (value: string) => void
  setPerPage: (value: string) => void
  hidePerPage: boolean
}

const Pagination: React.FC<PaginationProps> = ({ items, setPage, setPerPage, hidePerPage }) => {
  if (!items) return null;

  const { current_page, last_page, path, per_page } = items;

  const startPage = Math.max(current_page - 2, 1);
  const endPage = Math.min(startPage + 4, last_page);
  const pageNumbers = Array.from({ length: endPage - startPage + 1 }, (_, index) => startPage + index);

  const baseUrl = '';

  const handlePerPageChange = async (e: any) => {

    const value = e?.value || e?.target.value || undefined;
    console.log(value)
    setPerPage(value)
  };

  const handlePageClick = (data: any) => {
    const selectedPage = data.selected + 1;
    setPage(selectedPage.toString());
  };

  const options = [
    { value: 20, label: '20 per page' },
    { value: 50, label: '50 per page' },
    { value: 100, label: '100 per page' },
    { value: 200, label: '200 per page' },
  ]

  return (
    <nav className="d-flex mt-5 w-100">
      <div className="d-flex justify-content-center align-items-baseline bg-body-secondary p-2 w-100" aria-label="Page navigation" style={{ height: '3.4rem' }}>
        <ReactPaginate
          previousLabel={<Icon icon={'mingcute:arrows-left-line'} />}
          nextLabel={<Icon icon={'mingcute:arrows-right-line'} />}
          breakLabel={'...'}
          breakClassName={'break-me'}
          pageCount={last_page}
          marginPagesDisplayed={2}
          pageRangeDisplayed={5}
          onPageChange={handlePageClick}
          containerClassName={'pagination'}
          subContainerClassName={'pages pagination'}
          activeClassName={'active'}
        />
        {
          !hidePerPage
          &&
          <div>
            <Select
              key={0}
              className="form-control"
              classNamePrefix="select"
              placeholder="Select per page"
              defaultValue={per_page ? options.find(v => v.value == per_page) : options[1]}
              options={options}
              onChange={(v) => handlePerPageChange(v)}
            />
          </div>
        }

      </div>
    </nav>
  );
};

export default Pagination;
