import AutoTable from "@/components/Autos/AutoTable"
import GeneralModal from "@/components/Modals/GeneralModal"
import useAxios from "@/hooks/useAxios"
import { Icon } from "@iconify/react/dist/iconify.js"
import AsyncSelect from 'react-select/async';
import { useEffect, useState } from "react";
import { useRolePermissionsContext } from "@/contexts/RolePermissionsContext";
import { subscribe, unsubscribe } from "@/utils/events";
import { useAuth } from "@/contexts/AuthContext";

type Props = {
    role: RoleInterface | undefined;
    permissions: PermissionInterface[] | undefined;
    loadingPermission: boolean;
}

const Users = ({ role }: Props) => {

    const [key, setKey] = useState(0)

    return (
        <div>
            {
                role && role.id ?
                    <>
                        <div className='d-flex justify-content-between mt-2'>
                            <h4>Users list</h4>
                            <button type="button" className="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#addUserToRole">Add User to Role</button>
                        </div>
                        <AutoTable
                            key={key}
                            baseUri={`/dashboard/settings/users?role_id=${role.id}`}
                            columns={[
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
                            ]}
                            search={true}
                            tableId='roleUsersTable'
                        />
                        <GeneralModal setKey={setKey} title='Add User to Role' actionUrl={`dashboard/settings/role-permissions/roles/view/${role.id}/add-user`} id={`addUserToRole`}>
                            <AddUser key={key} role={role} />
                        </GeneralModal>
                    </>

                    :
                    <div>Loading...</div>
            }
        </div>
    )

}

const AddUser = ({ role }: Pick<Props, 'role'>) => {

    const { fetchRolesAndDirectPermissions } = useRolePermissionsContext();

    const { user } = useAuth()

    const { get } = useAxios()

    const loadOptions = async (q: string) => {
        const { data } = await get(`/dashboard/settings/users?role_id=${role.id}&negate=1&all=1&q=${q}`);

        return data ?? []
    }

    const handleIsCurrentUser = (event: CustomEvent<{ [key: string]: any }>) => {

        if (user && event.detail) {
            const detail = event.detail;

            if (detail.results) {
                if (detail.elementId === 'addUserToRole') {

                    if (user.id == detail.results.id) {
                        // refetch user roles
                        fetchRolesAndDirectPermissions()
                    }
                }
            }
        }
    };

    useEffect(() => {
        subscribe('ajaxPostDone', handleIsCurrentUser as EventListener);

        return () => unsubscribe('ajaxPostDone', handleIsCurrentUser as EventListener)
    }, [])

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
                <button type="submit" className="btn btn-info btn-raised submit-btn"><Icon icon="save"></Icon> Submit</button>
            </div>
        </div>
    )
}

export default Users