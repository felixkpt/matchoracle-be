import useAxios from '@/hooks/useAxios';
import { useEffect } from 'react';

const useRoleRoutes = (roleUri: string, routesUri: string) => {
  const { data: role, get: getRole } = useAxios<RoleData>();
  const { data: routes, get: getRoutes } = useAxios<Route[]>();

  useEffect(() => {
    if (roleUri) {
      getRole(roleUri);

    }
  }, [roleUri]);

  useEffect(() => {
    if (roleUri) {
      getRoutes(routesUri);
    }
  }, [roleUri]);

  return { role, routes };
};

export default useRoleRoutes;
