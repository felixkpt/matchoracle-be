import React from 'react';
import { Items } from '@/interfaces';
import { NavLink } from 'react-router-dom';

interface PaginationProps {
  items: Items | undefined;
  setPage: (value: string) => void
  setPerPage: (value: string) => void
}

const Pagination: React.FC<PaginationProps> = ({ items, setPage, setPerPage }) => {
  if (!items) return null;

  const { current_page, last_page, path, per_page } = items;

  const startPage = Math.max(current_page - 2, 1);
  const endPage = Math.min(startPage + 4, last_page);
  const pageNumbers = Array.from({ length: endPage - startPage + 1 }, (_, index) => startPage + index);

  const baseUrl = '';

  const handlePerPageChange = async (e: any) => {
    const value = e.target.value ? e.target.value : undefined;
    setPerPage(value)
  };

  const updateParams = (a: string, b: string) => {
    setPage(a)
    setPerPage(b)
  }

  return (
    <div className="d-flex justify-center mt-5 w-100">

      <nav className="d-flex w-100 bg-body-secondary" aria-label="Page navigation">

        <div className="d-flex p-2 w-100">
          <div id="states-button" data-dropdown-toggle="dropdown-states" className="flex-grow-1 z-10 inline-d-flex items-center py-2.5 px-4 text-sm font-medium text-center text-gray-500 bg-gray-100 border border-gray-300 rounded-l-lg hover:bg-gray-200 focus:ring-4 focus:outline-none focus:ring-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 dark:focus:ring-gray-700 dark:text-white dark:border-gray-600" type="button">
            <ul className="pagination">
              {/* Go back button */}
              <li className="page-item">
                {current_page > 1 ? (
                  <NavLink
                    to={`${baseUrl}?page=${startPage}&per_page=${per_page}`}
                    type="button"
                    className="page-link ml-0 rounded-l-lg border border-gray-300 bg-white py-2 px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white inline-d-flex"
                    onClick={(e) => {
                      e.preventDefault();
                      updateParams(startPage, per_page);
                    }}
                    aria-label="Previous"
                  >
                    <svg
                      stroke="currentColor"
                      fill="currentColor"
                      strokeWidth="0"
                      viewBox="0 0 20 20"
                      aria-hidden="true"
                      className="h-5 w-5"
                      height="1em"
                      width="1em"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        fillRule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clipRule="evenodd"
                      ></path>
                    </svg>
                    Go back
                  </NavLink>
                ) : (
                  <button
                    type="button"
                    className="ml-0 rounded-l-lg border border-gray-300 bg-white py-2 px-3 leading-tight text-gray-500 opacity-50 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 inline-d-flex"
                    disabled
                    aria-hidden="true"
                  >
                    <svg
                      stroke="currentColor"
                      fill="currentColor"
                      strokeWidth="0"
                      viewBox="0 0 20 20"
                      aria-hidden="true"
                      className="h-5 w-5"
                      height="1em"
                      width="1em"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        fillRule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clipRule="evenodd"
                      ></path>
                    </svg>
                    Go back
                  </button>
                )}
              </li>

              {/* Page numbers */}
              {pageNumbers.map((pageNumber) => (
                <li className="page-item" key={pageNumber}>
                  <NavLink
                    to={`${baseUrl}?page=${pageNumber}&per_page=${per_page}`}
                    type="button"
                    className={`page-link ${pageNumber === current_page ? 'font-semibold' : ''
                      }`}
                    onClick={(e) => {
                      e.preventDefault();
                      updateParams(pageNumber, per_page);
                    }}
                  >
                    {pageNumber}
                  </NavLink>
                </li>
              ))}

              {/* Go forward button */}
              <li>
                {current_page < last_page ? (
                  <NavLink
                    to={`${baseUrl}?page=${endPage}&per_page=${per_page}`}
                    type="button"
                    className="page-link rounded-r-lg border border-gray-300 bg-white py-2 px-3 leading-tight text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white inline-d-flex"
                    onClick={(e) => {
                      e.preventDefault();
                      updateParams(endPage, per_page);
                    }}
                    aria-label="Next"
                  >
                    Go forward
                    <svg
                      stroke="currentColor"
                      fill="currentColor"
                      strokeWidth="0"
                      viewBox="0 0 20 20"
                      aria-hidden="true"
                      className="h-5 w-5"
                      height="1em"
                      width="1em"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        fillRule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clipRule="evenodd"
                      ></path>
                    </svg>
                  </NavLink>
                ) : (
                  <button
                    type="button"
                    className="page-link rounded-r-lg border border-gray-300 bg-white py-2 px-3 leading-tight text-gray-500 opacity-50 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 inline-d-flex"
                    disabled
                    aria-hidden="true"
                  >
                    Go forward
                    <svg
                      stroke="currentColor"
                      fill="currentColor"
                      strokeWidth="0"
                      viewBox="0 0 20 20"
                      aria-hidden="true"
                      className="h-5 w-5"
                      height="1em"
                      width="1em"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        fillRule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clipRule="evenodd"
                      ></path>
                    </svg>
                  </button>
                )}
              </li>
            </ul>

          </div>
          <select value={per_page}
            onChange={handlePerPageChange}
            className="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-r-lg border-l-gray-100 dark:border-l-gray-700 border-l-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
            <option value="10">10 per page</option>
            <option value="20">20 per page</option>
            <option value="30">30 per page</option>
            <option value="50">50 per page</option>
            <option value="100">100 per page</option>
          </select>

        </div>
      </nav>
    </div>
  );
};

export default Pagination;
