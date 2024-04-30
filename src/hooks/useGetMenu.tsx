import { useState } from "react";
import useAxios from "./useAxios";

const useGetMenu = ({user, roles}: Props) => {

    const [currentRole, setCurrentRole] = useState<RoleInterface | undefined>();
    const { data, get: getMenu, loading, errors } = useAxios();
    const [userMenu, setUserMenu] = useState<RouteCollectionInterface[]>([]);

    if (user && currentRole === undefined && roles.length > 0) {
        const defaultRole = user.default_role_id ? roles.find((role) => String(role.id) === String(user.default_role_id)) : null;
        setCurrentRole(defaultRole || roles[0]);
    }

    let roleId = 0
    if (user && currentRole) {
        roleId = currentRole.id;
    }

    getMenu('/dashboard/settings/role-permissions/roles/view/' + roleId + '/get-role-menu/?get-menu=1').then((resp) => {
        if (resp === undefined) {
            setUserMenu([]);
        }
    });

    return {}
}

export default useGetMenu