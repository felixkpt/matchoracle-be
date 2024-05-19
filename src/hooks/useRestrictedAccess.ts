import { useEffect, useState } from "react";
import { useAuth } from "../contexts/AuthContext";
import useAxios from "./useAxios";
import { useRolePermissionsContext } from "../contexts/RolePermissionsContext";
import { useNavigate } from "react-router-dom";
import { convertToLaravelPattern } from '@/utils/helpers';
import { HttpVerbsType } from "../interfaces/UncategorizedInterfaces";
interface Props {
  uri: string
  permission?: string | null
  method?: HttpVerbsType
}

const useRestrictedAccess = ({ uri, permission, method }: Props) => {

  const allowedRoutes = ['error-404', 'login'];
  const testPermission = permission || uri;

  const [isAllowed, setIsAllowed] = useState(false);

  const [reloadKey, setReloadKey] = useState<number>(0);
  const { updateUser, deleteUser, setRedirectTo, setRedirectMessage } = useAuth();
  const { data: freshUser, loading: loadingUser, get: getUser, loaded: loadedUser, errors: loadingUserError } = useAxios();

  const navigate = useNavigate();
  const { guestMode, refreshedCurrentRole, currentRole, loadingCurrentRole, refreshCurrentRole, loadingRoutePermissions, refreshedRoutePermissions, directPermissions, routePermissions } = useRolePermissionsContext();

  const [checkedAccess, setCheckedAccess] = useState(false);

  const [previousUrl, setPreviousUrl] = useState<string | null>(null);

  const [startCheckingAccess, setStartCheckingAccess] = useState(false);

  // Scenario 1: route is in allowedRoutes
  useEffect(() => {

    if (allowedRoutes.includes(testPermission)) {
      setIsAllowed(true)
    }

  }, [])

  // Scenario 2: dealing with guest
  // useEffect to fetch user data and refresh current role
  useEffect(() => {
    if (!isAllowed && !loadedUser) {
      getUser('/auth/user?verify=1');
    }
  }, []);
  // useEffect to update user data or delete user on loadedUser state change
  useEffect(() => {
    if (loadedUser) {
      if (freshUser) {
        updateUser(freshUser);
      } else {
        if (loadingUserError == 'Unauthenticated.') {
          deleteUser();
        }
      }
    }
  }, [loadedUser]);

  useEffect(() => {
    if (!isAllowed && loadedUser && !freshUser && guestMode) {
      // is guest...
      setStartCheckingAccess(true)
    }

  }, [loadedUser, freshUser, loadingRoutePermissions])

  // Scenario 3: dealing with loggedin user
  useEffect(() => {
    if (!isAllowed && loadedUser && freshUser) {
      // is loggedin...
      setStartCheckingAccess(true)
    }

  }, [loadedUser, freshUser])

  // Scenario 4: Actual checking of access permission
  useEffect(() => {

    if (startCheckingAccess) {

      if (!isAllowed && !checkedAccess && routePermissions.length > 0) {
        const isAllowed = userCan(testPermission, method || 'get');
        setIsAllowed(isAllowed);
        setCheckedAccess(true);
      }
    }

  }, [startCheckingAccess, checkedAccess, routePermissions, testPermission])

  // Scenario 5: Guest / Auth sort
  useEffect(() => {

    if (startCheckingAccess && !loadingRoutePermissions) {

      if (!isAllowed && !loadingCurrentRole) {

        if (currentRole && routePermissions.length > 0) {
          const isAllowed = userCan(testPermission, method || 'get');
          setIsAllowed(isAllowed);
          setCheckedAccess(true);

          if (!isAllowed) {
            setRedirectMessage(`${currentRole.name} can't access: ${testPermission}`)
            navigate('/login')
          }

        } else if (!loadingRoutePermissions) {
          navigate('/login')
        }

      }

    }

  }, [routePermissions, startCheckingAccess, checkedAccess, refreshedRoutePermissions, freshUser, refreshedCurrentRole, currentRole, loadingRoutePermissions, guestMode, loadingCurrentRole])

  // Scenario 6: miscellenious
  // useEffect to update setRedirectTo & previous URL
  useEffect(() => {
    setRedirectTo(location.pathname);
    if (previousUrl !== location.pathname) {
      setPreviousUrl(location.pathname);
    }
  }, [location.pathname]);

  // useEffect to refreshCurrentRole on reloadKey change
  useEffect(() => {
    if (reloadKey > 0) {
      refreshCurrentRole();
    }
  }, [reloadKey]);

  const userCan = (permission: string, method: string) => {

    if (method) {
      permission = permission.replace(/\./g, '/')

      permission = convertToLaravelPattern(permission)
      const permissionCleaned = permission.replace(/\/$/, '').replace(/^\//, '')

      const httpMethod = method.toUpperCase()
      const found = !routePermissions ? false : !!routePermissions.find((route) => {
        return (String(route).startsWith(permissionCleaned + '@') && (httpMethod === 'ANY' || String(route).includes('@' + httpMethod))) || httpMethod === 'GET' && String(route) === permissionCleaned

      });
      return found
    } else {
      return !!directPermissions.some((perm) => perm.name === permission)
    }

  };

  return { userCan, loadingUser, loadedUser, loadingUserError, checkedAccess, isAllowed, loadingRoutePermissions, previousUrl, setReloadKey }
}

export default useRestrictedAccess