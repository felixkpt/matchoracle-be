import { useEffect, useState } from "react";
import AutoTabs from "@/components/AutoTabs";
import Permissions from "./Tabs/Permissions";
import Users from "./Tabs/Users";
import useAxios from "@/hooks/useAxios";
import { useParams } from "react-router-dom";
import Error404 from "@/Pages/ErrorPages/Error404";
import Loader from "@/components/Loader";

export default function Index(): JSX.Element {

    const { id } = useParams<{ id: string }>();

    const { loading, get } = useAxios();

    const [role, setRole] = useState<RoleInterface>()


    useEffect(() => {

        if (id) {
            getRecord()
        }
    }, [id])

    const getRecord = () => {

        get(`admin/settings/role-permissions/roles/view/${id}`).then((res) => {
            if (res) {
                setRole(res)
            }
        })
    }

    const tabs = [
        {
            name: "Permissions",
            link: "permissions",
            content: <Permissions role={role} />,
        },
        {
            name: "Users",
            link: "users",
            content: <Users role={role} />,
        },
    ];

    return (
        <div className="mb-3">

            {
                !loading ?

                    role ?
                        <div>
                            <AutoTabs title={`${role.name} role`} tabs={tabs} active="permissions" listUrl="/admin/settings/role-permissions/roles" />
                        </div>
                        :
                        <Error404 />
                    :
                    <Loader />
            }

        </div>
    );
}
