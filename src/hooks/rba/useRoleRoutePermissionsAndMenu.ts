import { useEffect, useState } from 'react';
import useGetUserRolesAndPermissions from './useGetUserRolesAndPermissions';
import useGetRoleRoutePermissions from './useGetRoleRoutePermissions';
import useGetRoleMenu from './useGetRoleMenu';

const useRoleRoutePermissionsAndMenu = () => {

    const { guestMode, isGuestModeSupported, user, loadingUser, loadedUser,
        loadingUserError, setUser, roles, routePermissions,
        directPermissions, currentRole, setCurrentRole, loading: loadingUserRolesAndPermissions,
        loaded: loadedUserRolesAndPermissions, errors: errorsLoadingUserRolesAndPermissions, reload: reloadUserRolesAndPermissions
    } = useGetUserRolesAndPermissions()

    const { roleRoutePermissions, loading: loadingRoutePermissions, loaded: loadedRoleRoutePermissions,
        errors: errorsLoadingRoutePermissions, reload: reloadRoleRoutePermissions, setCurrentRole: getRoleRoutePermissionsSetCurrentRole, key: roleRoutePermissionsKey
    } = useGetRoleRoutePermissions()

    const { roleMenu, expandedRootFolders, loading: loadingRoleMenu, loaded: loadedRoleMenu,
        errors: errorsLoadingRoleMenu, reload: reloadRoleMenu, setCurrentRole: getRoleMenuSetCurrentRole
    } = useGetRoleMenu()

    const [localRole, setLocalRole] = useState(currentRole)

    const [hasPermissions, setHasPermissions] = useState<boolean>(routePermissions.length > 0 || roleRoutePermissions.length > 0)
    useEffect(() => {
        setHasPermissions(routePermissions.length > 0 || roleRoutePermissions.length > 0)
    }, [routePermissions.length, roleRoutePermissions.length])

    useEffect(() => {

        if (localRole?.id && currentRole?.id != localRole.id) {
            console.log('APPLIED! currentRole', currentRole?.id, 'localRole', localRole?.id)
            getRoleRoutePermissionsSetCurrentRole(currentRole)
            setLocalRole(currentRole)
        }

    }, [currentRole])

    useEffect(() => {

        if (currentRole) {
            if (!localRole) {
                setLocalRole(currentRole)
            }
        }

        if (!hasPermissions) {
            getRoleRoutePermissionsSetCurrentRole(currentRole)
        }

    }, [loadedUserRolesAndPermissions, currentRole])

    useEffect(() => {
        if (hasPermissions) {
            getRoleMenuSetCurrentRole(currentRole)

        }
    }, [hasPermissions, currentRole])

    return {
        // useGetUserRolesAndPermissions
        roleAndPermissions: {
            // guest mode / auth mode
            guestMode,
            isGuestModeSupported,
            user,

            loadingUser, loadedUser, loadingUserError, setUser,
            roles,
            routePermissions,
            directPermissions,
            currentRole,
            setCurrentRole,
            loading: loadingUserRolesAndPermissions,
            loaded: loadedUserRolesAndPermissions,
            errors: errorsLoadingUserRolesAndPermissions,
            reload: reloadUserRolesAndPermissions,
        },
        
        // useGetRoleRoutePermissions
        roleRoutePermissions: {
            permissions: roleRoutePermissions,
            loading: loadingRoutePermissions,
            loaded: loadedRoleRoutePermissions,
            errors: errorsLoadingRoutePermissions,
            reload: reloadRoleRoutePermissions,
            key: roleRoutePermissionsKey,
        },

        // useGetRoleMenu
        roleMenu: {
            menu: roleMenu,
            expandedRootFolders,
            loading: loadingRoleMenu,
            loaded: loadedRoleMenu,
            errors: errorsLoadingRoleMenu,
            reload: reloadRoleMenu,
        }
    };
};

export default useRoleRoutePermissionsAndMenu;
