import { useEffect, useState } from "react";
import useAxios from "./useAxios";
import { config } from "@/utils/helpers";
import { PermissionInterface, RoleInterface } from "@/interfaces/RolePermissionsInterfaces";

const useFetchUserRolesAndDirectPermissions = () => {
    const { get, loaded } = useAxios();

    const [roles, setRoles] = useState<RoleInterface[]>([]);
    const [directPermissions, setDirectPermissions] = useState<PermissionInterface[]>([]);
    const [refresh, setRefresh] = useState<number>(0);

    useEffect(() => {
        get(config.urls.rolePermissions + '/role-permissions/roles/get-user-roles-and-direct-permissions').then((rolesPermissions) => {
            if (rolesPermissions) {
                setRoles(rolesPermissions.roles || []);
                setDirectPermissions(rolesPermissions.direct_permissions || []);
            }

        });
    }, [refresh])

    return { roles, directPermissions, setRefresh, loaded }
}

export default useFetchUserRolesAndDirectPermissions