import { useEffect } from "react";
import AutoTabs from "@/components/AutoTabs";
import Permissions from "./Tabs/Permissions";
import Users from "./Tabs/Users";
import useAxios from "@/hooks/useAxios";
import { useParams } from "react-router-dom";
import PageHeader from "@/components/PageHeader";

export default function Index(): JSX.Element {

    const { id } = useParams<{ id: string }>();

    const roleUri = `admin/settings/role-permissions/roles/view/${id}`;

    const { data: role, loading, get } = useAxios();

    useEffect(() => {

        get(roleUri)

    }, [])

    const permissionsUri = `admin/settings/role-permissions/permissions/get-role-permissions/${id}`;

    const { data: permissions, get: getPermissions, loading: loadingPermission } = useAxios<PermissionInterface[]>();

    useEffect(() => {
        doGetPermissions()
    }, [])

    function doGetPermissions() {
        getPermissions(permissionsUri, { uri: 1 });
    }

    const tabs = [
        {
            name: "Permissions",
            link: "permissions",
            content: <Permissions role={role} permissions={permissions} loadingPermission={loadingPermission} doGetPermissions={doGetPermissions} />,
        },
        {
            name: "Users",
            link: "users",
            content: <Users role={role} permissions={permissions} loadingPermission={loadingPermission} />,
        },
    ];

    return (
        <div className="mb-3">
            {
                !loading && role && <PageHeader title={role.name} listUrl="/admin/settings/role-permissions/roles" />
            }
             
            <AutoTabs tabs={tabs} active="permissions" />
        </div>
    );
}
