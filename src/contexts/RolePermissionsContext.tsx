import useRolePermissions from '@/hooks/useRolePermissions';
import { PermissionInterface, RoleInterface, RouteCollectionInterface } from '@/interfaces/RolePermissionsInterfaces';
import React, { createContext, useContext } from 'react';

interface RolePermissionsContextType {
    guestMode: boolean
    currentRole: RoleInterface
    setCurrentRole: (role: RoleInterface | undefined) => void
    loadingCurrentRole: boolean
    refreshCurrentRole: () => void;
    setRefreshedCurrentRole: (val: boolean) => React.Dispatch<React.SetStateAction<boolean>>;
    refreshedCurrentRole: boolean;
    
    roles: RoleInterface[];
    directPermissions: PermissionInterface[];
    routePermissions: PermissionInterface[];
    refreshedRoutePermissions: boolean;
    fetchRoutePermissions: (roleId?: number | string, source?: string) => void;
    loadingRoutePermissions: boolean
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
