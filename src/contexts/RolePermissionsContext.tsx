import useRolePermissions from '@/hooks/useRolePermissions';
import { PermissionInterface, RoleInterface, RouteCollectionInterface } from '@/interfaces/RolePermissionsInterfaces';
import React, { createContext, useContext } from 'react';

interface RolePermissionsContextType {
    roles: RoleInterface[];
    directPermissions: PermissionInterface[];
    routePermissions: PermissionInterface[];
    refreshCurrentRole: () => void;
    refreshedCurrentRole: boolean;
    setRefreshedCurrentRole: (val: boolean) => React.Dispatch<React.SetStateAction<boolean>>;
    refreshedRoutePermissions: boolean;
    fetchRoutePermissions: (roleId?: string, source?: string) => void;
    loadingRoutePermissions: boolean
    currentRole: RoleInterface
    setCurrentRole: (role: RoleInterface | undefined) => void
    roleWasChanged: boolean
    setRoleWasChanged: (val: boolean) => void
    userMenu: RouteCollectionInterface[]
    setUserMenu: (role: RouteCollectionInterface[] | undefined) => void
    expandedRootFolders: []
    loadingMenu: boolean,
    errorsLoadingMenu: string | boolean,
}

const RolePermissionsContext = createContext<RolePermissionsContextType | undefined>(undefined);

interface Props {
    children: JSX.Element
}

export const RolePermissionsProvider: React.FC<Props> = ({ children }) => {
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
        throw new Error(`useRolePermissionsContext must be used within a RolePermissionsProvider`);
    }
    return context;
};
