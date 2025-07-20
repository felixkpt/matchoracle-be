import { useEffect, useState } from "react";
import { useRoleRoutePermissionsAndMenuContext } from "@/contexts/RoleRoutePermissionsAndMenuContext";
import { useNavigate } from "react-router-dom";
import { convertToLaravelPattern } from '@/utils/helpers';
import { HttpVerbsType } from "@/interfaces/UncategorizedInterfaces";
import { PermissionInterface } from "@/interfaces/RolePermissionsInterfaces";

interface Props {
  uri: string;
  permission?: string | null;
  method?: HttpVerbsType;
}

const useRoleBasedAccessAccess = ({ uri, permission, method = 'get' }: Props) => {
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

  const [loading, setLoading] = useState(true);
  const [previousUrl, setPreviousUrl] = useState<string | null>(null);

  const perms = roleRoutePermissions.permissions.length > 0 ? roleRoutePermissions.permissions : roleAndPermissions.routePermissions;
  const [permissions, setPermissions] = useState<PermissionInterface[]>([]);

  // Update permissions when role or route permissions change
  useEffect(() => {
    if (permissions.length == 0 || roleRoutePermissions.key > 1) {
      const perms = roleRoutePermissions.permissions.length > 0 ? roleRoutePermissions.permissions : roleAndPermissions.routePermissions;
      setPermissions(perms);
      setIsAllowed(false);
    }
  }, [roleAndPermissions.routePermissions, roleRoutePermissions.key]);

  const [loadedPermissions, setLoadedPermissions] = useState(false);

  // Set loaded permissions status
  useEffect(() => {
    if (permissions.length > 0 || (roleRoutePermissions.loaded || !roleAndPermissions.isGuestModeSupported)) {
      setLoadedPermissions(true);
    }
  }, [permissions.length, roleAndPermissions.isGuestModeSupported]);

  // Handle allowed routes
  useEffect(() => {
    if (allowedRoutes.includes(testPermission)) {
      setScenario1(true);
      setIsAllowed(true);
      setLoading(false);
    }
  }, [testPermission, perms]);

  // Handle guest mode and no permissions
  useEffect(() => {
    if (!scenario1 && !user && loadedUser && guestMode && loadedPermissions) {
      if (permissions.length === 0) {
        setScenario2(true);
        setLoading(false);
        navigate('/login?r=snr1');
      }
    }
  }, [loadedPermissions, permissions, user, loadedUser, guestMode, scenario1, navigate]);

  // Handle scenario where user lacks permission
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
        setIsAllowed(true);
        setLoading(false);
      }
    }
  }, [loadedPermissions, permissions, scenario1, scenario2, user, loadedUser, guestMode, testPermission, method, loadedRoleRoutePermissions]);

  // Handle authenticated user with insufficient permissions
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

  // Handle authenticated user with sufficient permissions
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

  // Handle loading state when permissions are not fully loaded
  useEffect(() => {
    if (!scenario1 && !scenario2 && !scenario3 && !scenario4 && !scenario5 && user && loadedUser && !loadedPermissions) {
      setLoading(true);
    }
  }, [loadedPermissions, permissions, scenario1, scenario2, scenario3, scenario4, scenario5, user, loadedUser]);

  // Handle user loading errors
  useEffect(() => {
    if (loadingUserError && loadingUserError !== 'Unauthenticated.') {
      setIsAllowed(false);
      setLoading(false);
      navigate('/login?r=lus');
    }
  }, [loadingUserError, navigate]);

  // Check user permissions
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

  // Update previous URL
  useEffect(() => {
    if (previousUrl !== location.pathname) {
      setPreviousUrl(location.pathname);
    }
  }, [location.pathname]);

  return { loading, isAllowed, shouldRender403, reload, loadedRoleRoutePermissions, previousUrl };
};

export default useRoleBasedAccessAccess;
