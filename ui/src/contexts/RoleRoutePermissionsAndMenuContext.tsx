import useRoleRoutePermissionsAndMenu from '@/hooks/rba/useRoleRoutePermissionsAndMenu';
import { RoleRoutePermissionsAndMenuContextInterface } from '@/interfaces/RolePermissionsInterfaces';
import React, { createContext, useContext, ReactNode } from 'react';

// Create context with a more specific default value
const RoleRoutePermissionsAndMenuContext = createContext<RoleRoutePermissionsAndMenuContextInterface | undefined>(undefined);

interface Props {
    children: ReactNode; // Use ReactNode for children prop to allow more flexible child types
}

export const RoleRoutePermissionsAndMenuProvider: React.FC<Props> = ({ children }) => {
    const rolePermissionsAndMenu = useRoleRoutePermissionsAndMenu();

    return (
        <RoleRoutePermissionsAndMenuContext.Provider value={rolePermissionsAndMenu}>
            {children}
        </RoleRoutePermissionsAndMenuContext.Provider>
    );
};

export const useRoleRoutePermissionsAndMenuContext = (): RoleRoutePermissionsAndMenuContextInterface => {
    const context = useContext(RoleRoutePermissionsAndMenuContext);
    if (!context) {
        throw new Error('useRoleRoutePermissionsAndMenuContext must be used within a RoleRoutePermissionsAndMenuProvider');
    }
    return context;
};
