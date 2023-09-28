import useAxios from '@/hooks/useAxios';
import { emitAjaxPost } from '@/utils/helpers';
import React, { useEffect, useState } from 'react';

type Role = {
    id: number;
    name: string;
};

type User = {
    id: number;
    name: string;
    email: string;
    roles: Role[];
};

type Props = {};

const Create: React.FC<Props> = () => {
    const { data, loading, get } = useAxios();

    const [user, setUser] = useState<User | undefined>(undefined);
    const [roles, setRoles] = useState<Role[]>([]);
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');

    const handleRoleChange = (roleId: number) => {
        if (!user) return [];
        const selectedRoles = user.roles.map((role) => role.id);
        if (selectedRoles.includes(roleId)) {
            return selectedRoles.filter((id) => id !== roleId);
        }
        return [...selectedRoles, roleId];
    };

    useEffect(() => {
        get('admin/users/create');
    }, []);

    useEffect(() => {
        if (loading === false && data) {
            setUser(data?.user);
            setRoles(data?.roles);
        }
    }, [data, loading]);

    useEffect(() => {
        if (user) {
            setName(user.name);
            setEmail(user.email);
        }
    }, [user]);

    return (
        <div>
            <div className="container mx-auto card shadow p-3">
                <h1 className="text-2xl font-bold mb-4">{user ? 'Edit User' : 'Create User'}</h1>

                <form method='post' action={import.meta.env.VITE_APP_BASE_API + '/admin/users'} onSubmit={(e: any) => emitAjaxPost(e)} className="flex justify-center">

                    {user && <input type="hidden" value="put" name="method" />}

                    <div className="mb-4">
                        <label htmlFor="name" className="block text-gray-800 font-medium">
                            Name
                        </label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            className="form-input block w-full"
                            required
                            value={name}
                            onChange={(e) => setName(e.target.value)}
                        />
                    </div>
                    <div className="mb-4">
                        <label htmlFor="email" className="block text-gray-800 font-medium">
                            Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            className="form-input block w-full"
                            required
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
                        />
                    </div>

                    <div className="mb-4">
                        <label htmlFor="password" className="block text-gray-800 font-medium">
                            Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="password"
                            className="form-input block w-full"
                            required
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                        />
                    </div>
                    <>
                        <div className="mb-4">
                            <label htmlFor="password_confirmation" className="block text-gray-800 font-medium">
                                Confirm Password
                            </label>
                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                className="form-input block w-full"
                                value={passwordConfirmation}
                                onChange={(e) => setPasswordConfirmation(e.target.value)}
                            />
                        </div>
                    </>

                    <div className="mb-4">
                        <label htmlFor="roles" className="block text-gray-800 font-medium">
                            Roles
                        </label>
                        <select
                            name="roles[]"
                            id="roles"
                            className="form-multiselect block w-full"
                            multiple
                            onChange={(e) => handleRoleChange(Number(e.target.value))}
                        >
                            {roles.map((role) => (
                                <option
                                    key={role.id}
                                    value={role.id}
                                    selected={user && user.roles.some((r) => r.id === role.id)}
                                >
                                    {role.name}
                                </option>
                            ))}
                        </select>
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
        </div>
    );
};

export default Create;
