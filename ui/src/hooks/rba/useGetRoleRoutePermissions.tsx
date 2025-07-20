import { useEffect, useState } from "react";
import useAxios from "../useAxios";
import { config } from "@/utils/helpers";
import { PermissionInterface, RoleInterface } from "@/interfaces/RolePermissionsInterfaces";

const useGetRoleRoutePermissions = () => {

    const { get, loading, loaded, errors } = useAxios();

    const [currentRole, setCurrentRole] = useState<RoleInterface | undefined>();

    const [roleRoutePermissions, setRoleRoutePermissions] = useState<PermissionInterface[]>([]);
    const [refreshedRoutePermissions, setRefreshedRoutePermissions] = useState<boolean>(false);
    const [key, setKey] = useState<number>(0);
    const [reloadKey, setReloadKey] = useState<number>(0);

    const fetchRoutePermissions = async (roleId: number | string | undefined = undefined) => {

        if (!currentRole) {
            console.log('No currentRole!')
            return false
        }

        // When roleId is given, let us NOT refetch routePermissions if the following condition fails
        if (roleId && String(currentRole.id) !== roleId) return false;

        get(config.urls.rolePermissions + `/role-permissions/roles/view/${currentRole.id}/get-role-route-permissions?role-id=${currentRole.id}`).then((response) => {
            const data = response.results
            if (data) {
                setRoleRoutePermissions(data || []);
                setRefreshedRoutePermissions(true)
                setKey(key + 1)
            }

        });

    };

    useEffect(() => {
        if (currentRole) {
            fetchRoutePermissions()
        }
    }, [currentRole, reloadKey])

    function reload() {
        setReloadKey(reloadKey + 1)
    }

    return { roleRoutePermissions, refreshedRoutePermissions, loading, loaded, errors, reload, setCurrentRole, key }
}

export default useGetRoleRoutePermissions