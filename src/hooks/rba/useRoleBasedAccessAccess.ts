import { useEffect, useState } from "react";
import { useRoleRoutePermissionsAndMenuContext } from "@/contexts/RoleRoutePermissionsAndMenuContext";
import { useNavigate } from "react-router-dom";
import { convertToLaravelPattern } from '@/utils/helpers';
import { HttpVerbsType } from "@/interfaces/UncategorizedInterfaces";

interface Props {
  uri: string;
  permission?: string | null;
  method?: HttpVerbsType;
}

const useRoleBasedAccessAccess = ({ uri, permission, method }: Props) => {
  const allowedRoutes = ['error-404', 'login', 'user/account'];
  const testPermission = permission || uri;

  const [isAllowed, setIsAllowed] = useState(allowedRoutes.includes(testPermission));
  const [shouldRender403, setShouldRender403] = useState(false);

  const navigate = useNavigate();
  const { roleAndPermissions, roleRoutePermissions } = useRoleRoutePermissionsAndMenuContext();
  const { guestMode, user, loadedUser, loadingUserError, directPermissions } = roleAndPermissions;

  const { reload, loaded: loadedRoleRoutePermissions } = roleRoutePermissions;

  const [scenario1, setScenario1] = useState(isAllowed);
  const [scenario2, setScenario2] = useState(false);
  const [scenario3, setScenario3] = useState(false);
  const [scenario4, setScenario4] = useState(false);
  const [scenario5, setScenario5] = useState(false);
  const [scenario6, setScenario6] = useState(false);

  const [loading, setLoading] = useState(true);

  const [previousUrl, setPreviousUrl] = useState<string | null>(null);

  const perms = roleRoutePermissions.permissions.length > 0 ? roleRoutePermissions.permissions : roleAndPermissions.routePermissions
  const [permissions, setPermissions] = useState(perms)

  useEffect(() => {
    if (permissions.length == 0 || roleRoutePermissions.key > 1) {
      const perms = roleRoutePermissions.permissions.length > 0 ? roleRoutePermissions.permissions : roleAndPermissions.routePermissions
      setPermissions(perms)
      setIsAllowed(false)
    }
  }, [roleRoutePermissions.key])


  const [loadedPermissions, setLoadedPermissions] = useState(false)

  useEffect(() => {

    if (permissions.length > 0 || (roleRoutePermissions.loaded || !roleAndPermissions.isGuestModeSupported)) {
      setLoadedPermissions(true)
    }

  }, [permissions.length, roleAndPermissions.isGuestModeSupported])


  useEffect(() => {
    if (allowedRoutes.includes(testPermission)) {
      setScenario1(true);
      setIsAllowed(true);
      setLoading(false);
    }
  }, [testPermission, perms]);

  useEffect(() => {

    if (!scenario1 && !user && loadedUser && guestMode && loadedPermissions) {
      if (permissions.length === 0) {
        setScenario2(true);
        setLoading(false);
        navigate('/login?r=snr1');
      }
    }
  }, [loadedPermissions, permissions, user, loadedUser, guestMode, scenario1, navigate]);

  useEffect(() => {
    if (!scenario1 && !scenario2 && !user && loadedUser && guestMode && loadedPermissions) {
      const hasPermission = userCan(testPermission, method);
      if (!hasPermission) {
        setScenario3(true);
        if (loadedRoleRoutePermissions) {
          setShouldRender403(true);
        }
        setLoading(false);
      } else {
        console.log('first')
        setIsAllowed(true);
        setLoading(false);
      }
    }
  }, [loadedPermissions, permissions, scenario1, scenario2, user, loadedUser, guestMode, testPermission, method, loadedRoleRoutePermissions]);

  useEffect(() => {
    setShouldRender403(!isAllowed);
    if (!isAllowed && !scenario1 && !scenario2 && !scenario3 && user && loadedUser && loadedPermissions) {
      const hasPermission = userCan(testPermission, method);
      if (!hasPermission) {
        setScenario4(true);
        if (loadedRoleRoutePermissions) {
          setShouldRender403(true);
        }
        setIsAllowed(false);
        setLoading(false);
      } else {
        setIsAllowed(true);
        setLoading(false);
      }
    }
  }, [loadedPermissions, permissions, scenario1, scenario2, scenario3, user, loadedUser, testPermission, method, loadedRoleRoutePermissions]);

  useEffect(() => {
    if (!scenario1 && !scenario2 && !scenario3 && !scenario4 && user && loadedUser && loadedPermissions) {
      const hasPermission = userCan(testPermission, method);
      if (hasPermission) {
        setScenario5(true);
        setIsAllowed(true);
        setLoading(false);
      }
    }
  }, [loadedPermissions, permissions, scenario1, scenario2, scenario3, scenario4, user, loadedUser, testPermission, method]);

  useEffect(() => {
    if (!scenario1 && !scenario2 && !scenario3 && !scenario4 && !scenario5 && user && loadedUser && !loadedPermissions) {
      setScenario6(true);
      setLoading(true);
    }
  }, [loadedPermissions, permissions, scenario1, scenario2, scenario3, scenario4, scenario5, user, loadedUser,]);

  useEffect(() => {

    if (loadingUserError && loadingUserError !== 'Unauthenticated.') {
      setIsAllowed(false);
      setLoading(false);
      navigate('/login?r=lus');
    }
  }, [loadingUserError, navigate]);

  console.log('IS NOT ALLOWED::', isAllowed)

  useEffect(() => {
    if (scenario2) {
      console.log('Should redirect to login because of Scenario 2');
    }
  }, [scenario2]);

  useEffect(() => {

    if (scenario3 || scenario4) {
      console.log('Should render 403');
    }
  }, [scenario3, scenario4, shouldRender403]);

  useEffect(() => {
    if (scenario5) {
      console.log('Authenticated user with sufficient permissions');
    }
  }, [scenario5]);

  useEffect(() => {
    if (scenario6) {
      console.log('Waiting for role permissions to be fully loaded');
    }
  }, [scenario6]);

  useEffect(() => {
    console.log("isAllowed:", isAllowed);
  }, [isAllowed]);

  useEffect(() => {
    console.log("loading:", loading);
  }, [loading]);

  const userCan = (permission: string, method: HttpVerbsType | undefined) => {
    if (method) {
      permission = permission.replace(/\./g, '/');
      permission = convertToLaravelPattern(permission);
      const permissionCleaned = permission.replace(/\/$/, '').replace(/^\//, '');

      const httpMethod = method.toUpperCase();
      const found = !!permissions.find((route) => {
        return (String(route).startsWith(permissionCleaned + '@') && (httpMethod === 'ANY' || String(route).includes('@' + httpMethod))) || (httpMethod === 'GET' && String(route) === permissionCleaned);
      });
      return found;
    } else {
      return !!directPermissions.some((perm) => perm.name === permission);
    }
  };

  // useEffect to update previous URL
  useEffect(() => {
    if (previousUrl !== location.pathname) {
      setPreviousUrl(location.pathname);
    }
  }, [location.pathname]);


  return { loading, isAllowed, shouldRender403, reload, loadedRoleRoutePermissions, previousUrl };
};

export default useRoleBasedAccessAccess;
