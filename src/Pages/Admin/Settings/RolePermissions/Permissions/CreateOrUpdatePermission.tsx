import useAxios from '@/hooks/useAxios';
import { emitAjaxPost } from '@/utils/helpers';
import React, { useEffect, useState } from 'react';

type Role = {
    id: number;
    name: string;
};

type Permission = {
    id: number;
    name: string;
    email: string;
    roles: Role[];
};

type Props = {};

const CreateOrUpdatePermission: React.FC<Props> = () => {
    const { data, loading, get } = useAxios();

    const [permission, setPermission] = useState<Permission | undefined>(undefined);
    const [name, setName] = useState('');

    useEffect(() => {
        get('admin/settings/role-permissions/permissions/create');
    }, []);

    useEffect(() => {
        if (loading === false && data) {
            setPermission(data?.permission);
        }
    }, [data, loading]);

    useEffect(() => {
        if (permission) {
            setName(permission.name);
        }
    }, [permission]);

    return (
        <div>
            <div className="container mx-auto card shadow p-3">
                <h1 className="text-2xl font-bold mb-4">{permission ? 'Edit Permission' : 'Create Permission'}</h1>

                <form method='post' action={import.meta.env.VITE_APP_BASE_API + '/admin/settings/role-permissions/permissions'} onSubmit={(e: any) => emitAjaxPost(e)} className="flex justify-center">

                    {permission && <input type="hidden" value="put" name="method" />}

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
                    <div className='d-flex justify-content-end'>
                        <button
                            type="submit"
                            className="px-3 btn btn-outline-primary rounded hover:bg-indigo-600"
                        >
                            {permission ? 'Update Permission' : 'Create Permission'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default CreateOrUpdatePermission