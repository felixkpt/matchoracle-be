import useRolePermissions from '@/hooks/useRolePermissions';
import React, { createContext, useContext } from 'react';

interface RolePermissionsContextType {
    roles: RoleInterface[];
    directPermissions: PermissionInterface;
    routePermissions: PermissionInterface[];
    refreshCurrentRole: () => void;
    fetchRoutePermissions: (roleId?: string, source?: string) => void;
    loadingRoutePermissions: boolean
    currentRole: string
    setCurrentRole: (role: RoleInterface | undefined) => void
    roleWasChanged: boolean
    setRoleWasChanged: (val: boolean) => void
    userMenu:RouteCollectionInterface[]
    setUserMenu: (role: RouteCollectionInterface[] | undefined) => void
    loadingMenu: boolean,
    errorsLoadingMenu: any,
}

const RolePermissionsContext = createContext<RolePermissionsContextType | undefined>(undefined);

export const RolePermissionsProvider: React.FC = ({ children }) => {
    const rolePermissions = useRolePermissions();

    return (
        <RolePermissionsContext.Provider value={rolePermissions}>
            {children}
        </RolePermissionsContext.Provider>
    );
};

export const useRolePermissionsContext = () => {
    const context = useContext(RolePermissionsContext);
    if (!context) {
        throw new Error('useRolePermissionsContext must be used within a RolePermissionsProvider');
    }
    return context;
};
