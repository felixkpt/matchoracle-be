import React, { useEffect } from 'react';
import { Icon } from '@iconify/react/dist/iconify.js';
import { NavLink } from 'react-router-dom';
import Str from '@/utils/Str';
import { toggleSidebar } from './Index';

function cleanUri(uri: string): string {
   
    uri = `/${uri}`;
    uri = Str.before(uri, '@');
    return uri;
}

interface RoutesListProps {
    routes: RouteInterface[];
}

const RoutesList: React.FC<RoutesListProps> = ({ routes }) => {
    return (
        <>
            {routes.length > 0 && (
                <>
                    {routes.map((route, i) => (
                        <NavLink
                            onClick={(e:any) => toggleSidebar(e, 'hide')}
                            key={`${i}_${route.uri}`}
                            to={cleanUri(route.uri)}
                            className="nav-link overflow-hidden text-decoration-none px-3 cursor-pointer d-flex align-items-center gap-1"
                        >
                            <Icon icon={route.icon || 'carbon:list'} />
                            <span>{route.title}</span>
                        </NavLink>
                    ))}
                </>
            )}
        </>
    );
};

export default RoutesList;
