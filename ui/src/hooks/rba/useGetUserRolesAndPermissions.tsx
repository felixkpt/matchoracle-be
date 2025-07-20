import { useEffect, useState } from "react";
import useAxios from "../useAxios";
import { config } from "@/utils/helpers";
import { PermissionInterface, RoleInterface } from "@/interfaces/RolePermissionsInterfaces";
import { useAuth } from "@/contexts/AuthContext";

const useGetUserRolesAndPermissions = () => {
    const { user, setUser, updateUser, deleteUser } = useAuth();
    const { response, loading: loadingUser, get: getUser, loaded: loadedUser, errors: loadingUserError } = useAxios();
    const freshUser = response?.results
    const [stateUserId, setStateUserId] = useState<number | undefined>(user?.id)

    const [guestMode, setGuestMode] = useState<boolean>(true);
    const [isGuestModeSupported, setIsGuestModeSupported] = useState<boolean>(true);

    const { get, loaded, loading, errors } = useAxios();

    const [currentRole, setCurrentRole] = useState<RoleInterface | undefined>();
    const [reloadedCurrentRole, setReloadedCurrentRole] = useState<boolean>(false);

    const [roles, setRoles] = useState<RoleInterface[]>([]);
    const [routePermissions, setRoutePermissions] = useState<PermissionInterface[]>([]);
    const [directPermissions, setDirectPermissions] = useState<PermissionInterface[]>([]);
    const [reloadKey, setReloadKey] = useState<number>(0);

    // Scenario 2: dealing with guest
    // useEffect to fetch user data and refresh current role
    useEffect(() => {
        if (!loadedUser || !freshUser) {
            getUser('/auth/user?verify=1');
        }
    }, []);

    // useEffect to update user data or delete user on loadedUser state change
    useEffect(() => {
        if (loadedUser) {
            if (freshUser) {
                updateUser(freshUser);

                if (!stateUserId) {
                    setStateUserId(freshUser.id)
                }
                setReloadKey(reloadKey + 1)
                setGuestMode(false)

            } else {
                // User was reset and deleted in cache!
                setStateUserId(undefined)
                setReloadKey(reloadKey + 10)
                setGuestMode(true)
                deleteUser();
            }
        }
    }, [loadedUser]);

    useEffect(() => {
        if (loadedUser) {

            if (!stateUserId && user) {
                setStateUserId(user.id)
                setGuestMode(false)
                setReloadKey(reloadKey + 22)
            }

            if (stateUserId && !user) {
                // Scenario3 user changed to guestmode
                setReloadKey(reloadKey + 36)
                setCurrentRole(undefined)
                setRoles([])
                setStateUserId(undefined)
                setGuestMode(true)
            }
        }

    }, [user])

    useEffect(() => {

        if (roles.length > 0) {
            setReloadedCurrentRole(true)

            if (!currentRole || !user) {
                const defaultRole = user?.default_role_id ? roles.find((role) => String(role.id) === String(user.default_role_id)) : roles[0];
                setCurrentRole(defaultRole || roles[0]);
            }
        }

    }, [reloadKey, roles]);


    useEffect(() => {

        if (reloadKey > 0) {
            get(config.urls.rolePermissions + '/role-permissions/roles/get-user-roles-and-permissions?reason=' + reloadKey).then((response) => {
                const data = response.results

                if (data) {
                    setRoles(data.roles || []);
                    setIsGuestModeSupported(data.roles.length > 0)

                    setRoutePermissions(data.route_permissions || []);
                    setDirectPermissions(data.direct_permissions || []);
                } else if (response.status !== 200) {
                    setRoles([]);
                    setIsGuestModeSupported(false)

                    setRoutePermissions([]);
                    setDirectPermissions([]);
                }

            });
        }

    }, [reloadKey]);

    function reload() {
        setReloadKey(reloadKey + 1)
    }

    return { guestMode, isGuestModeSupported, user, loadingUser, loadedUser, loadingUserError, setUser, setCurrentRole, currentRole, roles, routePermissions, directPermissions, reloadedCurrentRole, loading, loaded, errors, reload }
}

export default useGetUserRolesAndPermissions