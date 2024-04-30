import { useEffect, useState } from "react";
import { useAuth } from "../contexts/AuthContext";
import useAxios from "./useAxios";
import { useRolePermissionsContext } from "../contexts/RolePermissionsContext";
import { useNavigate } from "react-router-dom";
import usePermissions from "./usePermissions";
import { HttpVerbsType } from "../interfaces/UncategorizedInterfaces";
interface Props {
  uri: string
  permission?: string | null
  method?: HttpVerbsType
}

const useRestrictedAccess = ({ uri, permission, method }: Props) => {

  const [reloadKey, setReloadKey] = useState<number>(0);
  const { updateUser, deleteUser, verified, setRedirectTo } = useAuth();
  const { data: freshUser, loading: loadingUser, get: getUser, loaded: loadedUser, errors: loadingUserError } = useAxios();

  const navigate = useNavigate();
  const { loadingRoutePermissions, currentRole, refreshCurrentRole, setCurrentRole, fetchRoutePermissions, routePermissions } = useRolePermissionsContext();
  const { userCan } = usePermissions();
  const allowedRoutes = ['error-404', 'login'];
  const [isAllowed, setIsAllowed] = useState(false);
  const [checked, setChecked] = useState(false);
  const [tried, setTried] = useState(false);
  const [previousUrl, setPreviousUrl] = useState<string | null>(null);
  const testPermission = permission || uri;

  // useEffect to check permission and loading state
  useEffect(() => {

    if ((verified || loadedUser) && testPermission && loadingRoutePermissions === false) {
      if (!allowedRoutes.includes(testPermission)) {
        const isAllowed = userCan(testPermission, method || 'get');
        setIsAllowed(isAllowed);
      }
      setChecked(true);
    }
  }, [verified, loadingRoutePermissions, permission, routePermissions, loadedUser]);

  // useEffect to fetch user data and refresh current role
  useEffect(() => {
    const fetchData = () => {
      getUser('/auth/user?verify=1');
    }

    if (verified === false) {
      setCurrentRole(undefined);
      fetchData();
    }
  }, [verified]);

  useEffect(() => {
    if (currentRole === undefined) {
      refreshCurrentRole();
    }
  }, [currentRole]);

  // useEffect to update user data or delete user on loading state change
  useEffect(() => {

    if (loadedUser) {
      if (tried === false) {
        setTried(true);
      } else {
        if (freshUser) {
          updateUser(freshUser);
        } else {
          deleteUser();
        }
      }
    }
  }, [loadedUser, tried]);

  // useEffect to update previous URL
  useEffect(() => {
    if (previousUrl !== location.pathname) {
      setPreviousUrl(location.pathname);
    }
  }, [location.pathname]);

  // useEffect to fetch route permissions on reloadKey change
  useEffect(() => {
    if (reloadKey > 0) {
      fetchRoutePermissions();
    }
  }, [reloadKey]);

  // useEffect to redirect to login if not allowed and loadingUser is false
  useEffect(() => {
    if (loadedUser && !isAllowed && checked) {
      setRedirectTo(location.pathname);
      navigate('/login');
    }
  }, [loadingUser, loadedUser, isAllowed, checked]);

  return { reloadKey, loadingUser, isAllowed, checked, loadingUserError, loadingRoutePermissions, previousUrl, setReloadKey }
}

export default useRestrictedAccess