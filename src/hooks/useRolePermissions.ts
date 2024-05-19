import { useEffect, useState } from 'react';
import useAxios from '@/hooks/useAxios';
import { useAuth } from '@/contexts/AuthContext';
import useFetchUserRolesAndDirectPermissions from './useFetchUserRolesAndDirectPermissions';
import { PermissionInterface, RoleInterface, RouteCollectionInterface } from '../interfaces/RolePermissionsInterfaces';
import { config } from '@/utils/helpers';

const useRolePermissions = () => {
    const { get } = useAxios();
    const { user } = useAuth();

    const [guestMode, setGuestMode] = useState<boolean>(true);
    const [loadingCurrentRole, setLoadingCurrentRole] = useState(true);
    const [currentRole, setCurrentRole] = useState<RoleInterface | undefined>();
    const [refreshedCurrentRole, setRefreshedCurrentRole] = useState<boolean>(false);

    const [refreshedRoutePermissions, setRefreshedRoutePermissions] = useState<boolean>(false);
    const [routePermissions, setRoutePermissions] = useState<PermissionInterface[]>([]);
    const [loadingRoutePermissions, setLoadingRoutePermissions] = useState(true);
    const [userMenu, setUserMenu] = useState<RouteCollectionInterface[]>([]);
    const [expandedRootFolders, setExpandedRootFolders] = useState<string>('');
    const { roles, directPermissions, setRefresh: refreshUserRolesAndDirectPermissions, loaded: loadedUserRolesAndDirectPermissions } = useFetchUserRolesAndDirectPermissions()


    const fetchRoutePermissions = async (roleId: number | string | undefined = undefined) => {

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
        } finally {
            setLoadingRoutePermissions(false);
        }

    };

    async function refreshCurrentRole() {

        if (user) {

            setCurrentRole(() => {
                refreshUserRolesAndDirectPermissions((curr: number) => curr = curr + 1);
                return undefined;
            });
        }

        setRefreshedCurrentRole(true)
        setLoadingCurrentRole(true)

    }

    useEffect(() => {
        if (user) {
            setGuestMode(false)
            refreshUserRolesAndDirectPermissions((curr: number) => curr = curr + 1);
        }
    }, [user])

    useEffect(() => {

        if (roles.length > 0) {
            setRefreshedCurrentRole(true)

            const defaultRole = user?.default_role_id ? roles.find((role) => String(role.id) === String(user.default_role_id)) : roles[0];
            setCurrentRole(defaultRole || roles[0]);
            setLoadingCurrentRole(false)

            fetchRoutePermissions();
        }

        if (roles.length === 0 && loadedUserRolesAndDirectPermissions) {
            setLoadingRoutePermissions(false)
            if (!currentRole) {
                setLoadingCurrentRole(false)
            }
        }

    }, [user, roles, currentRole, guestMode]);

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
        loadingCurrentRole,
        currentRole,
        setCurrentRole,
        guestMode,
        refreshCurrentRole,
        refreshedCurrentRole,
        setRefreshedCurrentRole,
        refreshedRoutePermissions,

        user,
        roles,
        directPermissions,
        routePermissions,
        fetchRoutePermissions,
        loadingRoutePermissions,
        userMenu,
        setUserMenu,
        expandedRootFolders,
        loadingMenu: loading,
        errorsLoadingMenu: errors,
        setRefreshedRoutePermissions,
    };
};

export default useRolePermissions;
