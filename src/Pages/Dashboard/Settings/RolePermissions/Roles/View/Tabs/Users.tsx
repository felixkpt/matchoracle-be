import AutoTable from "@/components/Autos/AutoTable"
import GeneralModal from "@/components/Modals/GeneralModal"
import useAxios from "@/hooks/useAxios"
import { Icon } from "@iconify/react/dist/iconify.js"
import AsyncSelect from 'react-select/async';
import { useEffect, useState } from "react";
import { useRoleRoutePermissionsAndMenuContext } from "@/contexts/RoleRoutePermissionsAndMenuContext";
import { useAuth } from "@/contexts/AuthContext";
import SubmitButton from "@/components/SubmitButton";
import useAutoPostDone from "@/hooks/autos/useAutoPostDone";
import { ColumnInterface } from "@/interfaces/UncategorizedInterfaces";
import { RoleInterface } from "@/interfaces/RolePermissionsInterfaces";
import useListSources from "@/hooks/list-sources/useListSources";
import Loader from "@/components/Loader";

type Props = {
    role: RoleInterface | undefined;
}

const Users = ({ role }: Props) => {

    const [key, setKey] = useState(0)

    const { event } = useAutoPostDone()
    const { user } = useAuth()

    const { roleAndPermissions } = useRoleRoutePermissionsAndMenuContext()

    useEffect(() => {
        if (event && (event.id == 'addUserToRole' || event.id == 'roleUsersForm')) {
            if (user?.id == event.data?.id) {
                // waiting for modal to close
                setTimeout(() => {
                    roleAndPermissions.reload()
                }, 200);
            }
        }
    }, [event])

    const columns: ColumnInterface[] = [
        {
            label: 'ID',
            key: 'id',
        },
        {
            label: 'User Name',
            key: 'name',
        },
        {
            label: 'Roles',
            key: 'Roles',
        },
        {
            label: 'Created At',
            key: 'created_at',
        },
        {
            label: 'Action',
            key: 'action',
        },
    ]

    const { rolePermissions } = useListSources()

    return (
        <div>
            {
                role && role.id ?
                    <>
                        <div className='d-flex justify-content-between mt-2'>
                            <h4>Users list</h4>
                            <button type="button" className="btn btn-info" data-bs-toggle="modal" data-bs-target="#addUserToRole">Add User to Role</button>
                        </div>
                        <AutoTable
                            key={key}
                            baseUri={`/dashboard/settings/users?role_id=${role.id}`}
                            columns={columns}
                            search={true}
                            tableId='roleUsersTable'
                            listSources={rolePermissions}
                        />
                        <GeneralModal setKey={setKey} title='Add User to Role' actionUrl={`dashboard/settings/role-permissions/roles/view/${role.id}/add-user`} id={`addUserToRole`}>
                            <AddUser key={key} role={role} />
                        </GeneralModal>
                    </>

                    :
                    <Loader />
            }
        </div>
    )

}

const AddUser = ({ role }: Pick<Props, 'role'>) => {

    const { get } = useAxios()

    const loadOptions = async (q: string) => {
        if (!role) return []

        const { data } = await get(`/dashboard/settings/users?role_id=${role.id}&negate=1&all=1&q=${q}`);

        return data.data ?? []
    }

    return (
        <div>
            <div className="form-group mb-2">
                <AsyncSelect
                    className="form-control"
                    name={`user_id`}
                    defaultOptions
                    loadOptions={(q: any) => loadOptions(q)}
                    getOptionValue={(option: any) => `${option['id']}`}
                    getOptionLabel={(option: any) => `${option['name']}`}
                />
            </div>
            <div className="form-group">
                <SubmitButton className="btn btn-info submit-btn"><Icon icon="save"></Icon> Submit</SubmitButton>
            </div>
        </div>
    )
}

export default Users