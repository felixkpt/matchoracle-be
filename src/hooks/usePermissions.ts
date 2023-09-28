import Str from '@/utils/Str';
import { useEffect, useState } from 'react';
import { useRolePermissionsContext } from '@/contexts/RolePermissionsContext';
import { convertToLaravelPattern } from '@/utils/helpers';

const usePermissions = () => {
    const { routePermissions, directPermissions } = useRolePermissionsContext();

    const [loading, setLoading] = useState(true)

    useEffect(() => {

        if (routePermissions.length > 0) {
            setLoading(false)
        }

    }, [routePermissions])

    const checkPermission = (permission: string, method: string) => {

        if (method) {
            permission = permission.replace(/\./g, '/')
            
            permission = convertToLaravelPattern(permission)

            const permissionCleaned = permission == '/' ? 'admin' : Str.afterLast(permission, 'admin/').replace(/\/$/, '')

            const httpMethod = method.toUpperCase()
            let found = !!routePermissions.find((route) => String(route).startsWith(permissionCleaned + '@') && String(route).includes('@' + httpMethod));
            return found
        } else {
            return !!directPermissions.some((perm) => perm.name === permission)
        }

    };

    return { loading, checkPermission };
};

export default usePermissions;
