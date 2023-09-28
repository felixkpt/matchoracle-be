import { useLocation } from 'react-router-dom';

// Custom hook to get and manage query parameters
function useQueryParams() {
  const location = useLocation();
  const queryParams = new URLSearchParams(location.search);

  // Function to get a specific query parameter
  function get(paramName: string): string | null {
    return queryParams.get(paramName);
  }

  // Function to set a query parameter
  function set(paramName: string, paramValue: string) {
    queryParams.set(paramName, paramValue);
    const newSearch = queryParams.toString();
    const newPath = `${location.pathname}?${newSearch}`;
    window.history.replaceState({}, '', newPath);
  }

  // Function to remove a query parameter
  function remove(paramName: string) {
    queryParams.delete(paramName);
    const newSearch = queryParams.toString();
    const newPath = `${location.pathname}?${newSearch}`;
    window.history.replaceState({}, '', newPath);
  }

  return {
    get,
    set,
    remove,
  };
}

export default useQueryParams;
