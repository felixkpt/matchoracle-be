import useAxios from '@/hooks/useAxios';
import { emitAjaxPost } from '@/utils/helpers';
import React, { useEffect, useRef, useState } from 'react';
import { useParams } from 'react-router-dom';
import Select from 'react-select';

type Props = {};


const options = [
    { value: 'chocolate', label: 'Chocolate' },
    { value: 'strawberry', label: 'Strawberry' },
    { value: 'vanilla', label: 'Vanilla' },
];

const CreateOrUpdateUser: React.FC<Props> = () => {
    const { id } = useParams<{ id: string }>();
    const { data: user, loading: loadingUser, get: getUser } = useAxios();
    const { data: roles, loading: loadingRoles, get: getRoles } = useAxios();

    const [name, setName] = useState('');
    const [email, setEmail] = useState('');

    const [selectedOptions, setselectedOptions] = useState<any[]>()

    const rolesRef = useRef()


    useEffect(() => {
        // Fetch the user data if the 'id' parameter is present

        if (id) {
            getUser(`dashboard/setting/users/view/${id}/edit`);
        }

    }, [id]);

    useEffect(() => {
        if (!roles) {
            getRoles(`dashboard/role-permissions/roles?all=1`);
        }
    }, [roles]);

    useEffect(() => {
        if (user) {

            setName(user.name)
            setEmail(user.email)

            const currentUserRoles = user.roles.map((role) => role);
            setselectedOptions(currentUserRoles)
            console.log(currentUserRoles)

        }

    }, [user])

    const handleRoleChange = (roles: any) => {

        if (!user) return [];

        const selectedRoles = roles.map((role) => role.id);

        if (rolesRef.current) {
            rolesRef.current.value = JSON.stringify(selectedRoles)
        }

        return [...selectedRoles];

    };

    return (
        <div>
            {
                user && Object.keys(user).length > 0

                    ? <div className="container mx-auto card shadow p-3">
                        <h1 className="text-2xl font-bold mb-4">{'Edit User'}</h1>
                        <form
                            method="post"
                            action-url={
                                user
                                    ? `/dashboard/setting/users/view/${user.id}`
                                    : '/dashboard/setting/users'
                            }
                            onSubmit={(e: any) => emitAjaxPost(e)}
                            className="flex justify-center"
                        >
                            {user && <input type="hidden" value="put" name="_method" />}

                            <div className="mb-4 form-group">
                                <label htmlFor="name" className="block text-gray-800 font-medium">
                                    Name
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    className='form-control'
                                    required
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                />
                            </div>
                            <div className="mb-4 form-group">
                                <label htmlFor="email" className="block text-gray-800 font-medium">
                                    Email
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    className="form-control"
                                    required
                                    value={email}
                                    onChange={(e) => setEmail(e.target.value)}
                                />
                            </div>

                            <div className="mb-4">
                                <label htmlFor="roles" className="block text-gray-800 font-medium">
                                    Roles
                                </label>
                                {
                                    roles && selectedOptions &&
                                    <Select
                                        defaultValue={selectedOptions}
                                        onChange={handleRoleChange}
                                        options={roles}
                                        getOptionValue={(option) => `${option['id']}`}
                                        getOptionLabel={(option) => `${option['name']}`}
                                        isMulti
                                    />
                                }

                                <input type="hidden" name="roles" defaultValue={JSON.stringify(selectedOptions?.map(role => role.id))} ref={rolesRef} />
                            </div>

                            <div className='d-flex justify-content-end'>
                                <button
                                    type="submit"
                                    className="px-3 btn btn-outline-primary rounded hover:bg-indigo-600"
                                >
                                    {user ? 'Update User' : 'Create User'}
                                </button>
                            </div>
                        </form>
                    </div>
                    :
                    <div>Getting user info</div>
            }

        </div>
    );
};

export default CreateOrUpdateUser;
