import { useEffect, useState } from 'react';
import useAxios from '@/hooks/useAxios';
import { useAuth } from '@/contexts/AuthContext';
import useFetchUserRolesAndDirectPermissions from './useFetchUserRolesAndDirectPermissions';
import { PermissionInterface, RoleInterface, RouteCollectionInterface } from '../interfaces/RolePermissionsInterfaces';
import { config } from '@/utils/helpers';

const useRolePermissions = () => {
    const { get } = useAxios();
    const { user, verified } = useAuth();

    const [currentRole, setCurrentRole] = useState<RoleInterface | undefined>();
    const [refreshedCurrentRole, setRefreshedCurrentRole] = useState<boolean>(false);
    const [refreshedRoutePermissions, setRefreshedRoutePermissions] = useState<boolean>(false);
    const [routePermissions, setRoutePermissions] = useState<PermissionInterface[]>([]);
    const [loadingRoutePermissions, setLoadingRoutePermissions] = useState(true);
    const [userMenu, setUserMenu] = useState<RouteCollectionInterface[]>([]);
    const [expandedRootFolders, setExpandedRootFolders] = useState<string>('');
    const { roles, directPermissions, setRefresh: refreshUserRolesAndDirectPermissions, loaded: loadedUserRolesAndDirectPermissions } = useFetchUserRolesAndDirectPermissions()

    const fetchRoutePermissions = async (roleId = null) => {

        if (!currentRole) {
            return false
        }

        // When roleId is given, let us NOT refetch routePermissions if the following condition fails
        if (roleId && String(currentRole.id) !== roleId) return false;

        setLoadingRoutePermissions(true);

        try {
            const routePermissionsResponse = await get(config.urls.rolePermissions + `/role-permissions/roles/view/${currentRole.id}/get-role-route-permissions`);

            if (routePermissionsResponse && !routePermissionsResponse.status) {
                setRoutePermissions(routePermissionsResponse || []);
                setRefreshedRoutePermissions(true)
            }
        } catch (error) {
            // Handle error
        }

        setLoadingRoutePermissions(false);
    };

    async function refreshCurrentRole() {

        if (user) {
            setCurrentRole(() => {
                refreshUserRolesAndDirectPermissions((curr: number) => curr = curr + 1);
                return undefined;
            });
        }
        setLoadingRoutePermissions(false)

        setRefreshedCurrentRole(true)

    }

    useEffect(() => {

        if (roles.length > 0) {
            const defaultRole = user?.default_role_id ? roles.find((role) => String(role.id) === String(user.default_role_id)) : roles[0];
            setCurrentRole(defaultRole || roles[0]);
            setRefreshedCurrentRole(true)
        } else {
            setLoadingRoutePermissions(false)
        }

    }, [loadedUserRolesAndDirectPermissions, verified, roles]);

    useEffect(() => {
        if (currentRole && roles.length > 0) {
            fetchRoutePermissions();
        }
    }, [roles, currentRole]);

    const { data, get: getMenu, loading, errors } = useAxios();

    useEffect(() => {
        if (currentRole) {
            getMenu(config.urls.rolePermissions + '/role-permissions/roles/view/' + currentRole.id + '/get-role-menu/?get-menu=1').then((resp) => {
                if (resp === undefined) {
                    setUserMenu([]);
                }
            });
        }

    }, [currentRole]);

    useEffect(() => {
        if (!loading && !errors && data) {
            setUserMenu(data?.menu);
            setExpandedRootFolders(data?.expanded_root_folders);
        }
    }, [loading]);

    return {
        user,
        roles,
        directPermissions,
        routePermissions,
        refreshCurrentRole,
        refreshedCurrentRole,
        setRefreshedCurrentRole,
        refreshedRoutePermissions,
        currentRole,
        setCurrentRole,
        fetchRoutePermissions,
        loadingRoutePermissions,
        userMenu,
        setUserMenu,
        expandedRootFolders,
        loadingMenu: loading,
        errorsLoadingMenu: errors,
    };
};

export default useRolePermissions;
