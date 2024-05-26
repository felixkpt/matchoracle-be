import { useEffect, useState } from "react";
import useAxios from "../useAxios";
import { config } from "@/utils/helpers";
import { RoleInterface, RouteCollectionInterface } from "@/interfaces/RolePermissionsInterfaces";

const useGetRoleMenu = () => {
    const { get, loaded, loading, errors } = useAxios();
    const [currentRole, setCurrentRole] = useState<RoleInterface | undefined>();

    const [roleMenu, setUserMenu] = useState<RouteCollectionInterface[]>([]);
    const [expandedRootFolders, setExpandedRootFolders] = useState<string>('');
    const [reloadKey, setReloadKey] = useState<number>(0);

    useEffect(() => {

        if (loading) {
            setUserMenu([])
        }

    }, [loading]);

    useEffect(() => {

        if (currentRole) {
            get(config.urls.rolePermissions + '/role-permissions/roles/view/' + currentRole.id + '/get-role-menu/?get-menu=1').then((response) => {
                const data = response.results
                if (data === undefined) {
                    setUserMenu([]);
                } else {
                    setUserMenu(data.menu);
                    setExpandedRootFolders(data.expanded_root_folders);
                }
            });
        }

    }, [currentRole, reloadKey]);

    function reload() {
        setReloadKey(reloadKey + 1)
    }

    return { roleMenu, expandedRootFolders, loading, loaded, errors, reload, setCurrentRole }
}

export default useGetRoleMenu