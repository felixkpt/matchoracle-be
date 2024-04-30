import { useEffect, useState } from "react";
import useAxios from "./useAxios";

const useFetchUserRolesAndDirectPermissions = () => {
    const { get } = useAxios();

    const [roles, setRoles] = useState<RoleInterface[]>([]);
    const [directPermissions, setDirectPermissions] = useState<PermissionInterface[]>([]);
    const [refresh, setRefresh] = useState<number>(0);

    useEffect(() => {
        get('/dashboard/settings/role-permissions/roles/get-user-roles-and-direct-permissions').then((rolesPermissions: any) => {
            if (rolesPermissions) {
                setRoles(rolesPermissions.roles || []);
                setDirectPermissions(rolesPermissions.direct_permissions || []);
            }

        });
    }, [refresh])

    return { roles, directPermissions, setRefresh }
}

export default useFetchUserRolesAndDirectPermissions