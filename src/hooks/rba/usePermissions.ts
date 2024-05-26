import { useEffect, useState } from 'react';
import { useRoleRoutePermissionsAndMenuContext } from '@/contexts/RoleRoutePermissionsAndMenuContext';
import { convertToLaravelPattern } from '@/utils/helpers';

const usePermissions = () => {
    const { roleAndPermissions, roleRoutePermissions } = useRoleRoutePermissionsAndMenuContext();
    const { directPermissions, } = roleAndPermissions;
    const { permissions } = roleRoutePermissions;

    const [loading, setLoading] = useState(true)

    useEffect(() => {

        if (permissions.length > 0) {
            setLoading(false)
        }

    }, [permissions])

    const userCan = (permission: string, method: string) => {

        if (method) {
            permission = permission.replace(/\./g, '/')

            permission = convertToLaravelPattern(permission)
            const permissionCleaned = permission.replace(/\/$/, '').replace(/^\//, '')

            const httpMethod = method.toUpperCase()
            const found = !!permissions.find((route) => {
                return (String(route).startsWith(permissionCleaned + '@') && (httpMethod === 'ANY' || String(route).includes('@' + httpMethod))) || httpMethod === 'GET' && String(route) === permissionCleaned

            });
            return found
        } else {
            return !!directPermissions.some((perm) => perm.name === permission)
        }

    };

    return { loading, userCan };
};

export default usePermissions;
