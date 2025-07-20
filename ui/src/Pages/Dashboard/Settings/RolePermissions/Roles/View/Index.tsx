import { useEffect, useState } from "react";
import AutoTabs from "@/components/Autos/AutoTabs";
import Permissions from "./Tabs/Permissions";
import Users from "./Tabs/Users";
import useAxios from "@/hooks/useAxios";
import { useParams } from "react-router-dom";
import Error404 from "@/Pages/ErrorPages/Error404";
import Loader from "@/components/Loader";
import { RoleInterface } from "@/interfaces/RolePermissionsInterfaces";

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

        get(`dashboard/settings/role-permissions/roles/view/${id}`).then((resp) => {
            if (resp.results) {
                setRole(resp.results)
            }
        })
    }

    const tabs = [
        {
            name: "Permissions",
            link: "permissions",
            component: <Permissions role={role} />,
        },
        {
            name: "Users",
            link: "users",
            component: <Users role={role} />,
        },
    ];

    return (
        <div className="mb-3">

            {
                !loading ?

                    role ?
                        <div>
                            <AutoTabs title={`${role.name} role`} tabs={tabs} />
                        </div>
                        :
                        <Error404 />
                    :
                    <Loader />
            }

        </div>
    );
}
