import { useEffect, useState } from 'react';
import useAxios from '@/hooks/useAxios';
import { useAuth } from '@/contexts/AuthContext';

const useRolePermissions = () => {
    const { get } = useAxios();
    const { user, verified } = useAuth()

    const [roles, setRoles] = useState<RoleInterface[]>([]);
    const [currentRole, setCurrentRole] = useState<RoleInterface | undefined>();
    const [directPermissions, setDirectPermissions] = useState<PermissionInterface[]>([]);
    const [routePermissions, setRoutePermissions] = useState<PermissionInterface[]>([]);

    const [loadingRoutePermissions, setLoadingRoutePermissions] = useState(false)

    const [userMenu, setUserMenu] = useState<RouteCollectionInterface[]>([])

    const fetchRolesAndDirectPermissions = async () => {

        if (!user) return false

        try {
            const rolesPermissions = await get('/admin/settings/role-permissions/roles/get-user-roles-and-direct-permissions');

            if (rolesPermissions) {
                setRoles(rolesPermissions.roles || []);
                setDirectPermissions(rolesPermissions.direct_permissions || []);
            }
        } catch (error) {
            // Handle error
        }
    };

    const fetchRoutePermissions = async (roleId = null) => {

        if (!verified || !currentRole) return false

        // When roleId is give, let us NOT do refetching routePermissions if following condition fails
        if (roleId && String(currentRole.id) !== roleId) return false

        setLoadingRoutePermissions(true)

        try {
            const routePermissionsResponse = await get(`/admin/settings/role-permissions/roles/view/${currentRole.id}/get-user-route-permissions`);

            if (routePermissionsResponse) {
                setRoutePermissions(routePermissionsResponse || []);
            }
        } catch (error) { }

        setLoadingRoutePermissions(false)
    };

    function refreshCurrentRole() {

        if (user) {

            // console.log('Resetting current role && fetchRolesAndDirectPermissions...')
            setCurrentRole(() => {
                fetchRolesAndDirectPermissions()
                return undefined
            });

        }
    }

    useEffect(() => {

        if (user && currentRole === undefined && verified && roles.length > 0) {

            // console.log(user)

            // console.log('Setting current role...')
            const defaultRole = user.default_role_id
                ? roles.find((role) => role.id === user.default_role_id)
                : null;

            setCurrentRole(defaultRole || roles[0]);
        }

    }, [roles, verified])

    useEffect(() => {
        if (user && currentRole && verified && roles.length > 0) {
            // console.log('FetchRoutePermissions...')
            fetchRoutePermissions();
        }

    }, [roles, currentRole])


    const { data, get: getMenu, loading, errors } = useAxios()
    useEffect(() => {

        if (user && currentRole) {
            getMenu('/admin/settings/role-permissions/roles/view/' + currentRole.id + '/get-role-menu/?get-menu=1').then((resp) => {
                if (resp === undefined) {
                    setUserMenu([])
                }
            })

        }

    }, [currentRole])

    useEffect(() => {

        if (!loading && !errors && data) {
            setUserMenu(data?.menu)
        }

    }, [loading])

    return {
        user,
        roles,
        directPermissions,
        routePermissions,
        refreshCurrentRole,
        currentRole,
        setCurrentRole,
        fetchRoutePermissions,
        loadingRoutePermissions,
        userMenu,
        setUserMenu,
        loadingMenu: loading,
        errorsLoadingMenu: errors,
    };
};

export default useRolePermissions;
