import { RouteCollectionInterface } from '@/interfaces/RolePermissionsInterfaces';
import Str from '@/utils/Str';
import { Icon } from '@iconify/react/dist/iconify.js';
import React from 'react';
import { NavLink } from 'react-router-dom';

interface Props {
  routes: RouteCollectionInterface[];
}

function handleToggle(key: string) {
  const target = document.getElementById(key);
  target?.classList.toggle('d-none');
}

const MenuRoutesTree: React.FC<Props> = ({ routes }) => {
  return (
    <ul className='list-unstyled nested-routes main'>
      {renderRoutes(routes, 0, '')}
    </ul>
  );
};

const renderRoutes = (
  routes: RouteCollectionInterface[],
  indent = 0,
  prevFolderName = ''
) => {
  indent += 2;

  return routes.map((route) => {
    const { children, routes: routeList, title, icon, slug } = route;

    if (routeList.length < 1 && (!children || children.length < 1)) return null;

    const currentId = Str.slug((prevFolderName + '/' + slug).replace(/^\//, '')) + '_nav';

    const isResolvableURI = (uri: string) => {
      const urisWithParams = uri.split('|').filter(uri => uri.includes('{'));
      const hasGetOrHead = uri.includes('@GET') || uri.includes('@HEAD');
      return urisWithParams.length === 0 && hasGetOrHead;
    };

    const filteredRouteList = routeList.filter(route => isResolvableURI(route.uri) && !route.hidden);

    function cleanUri(uri: string) {
      uri = `${uri.startsWith('admin') ? '' : 'admin/'}${uri}`;
      uri = Str.before(uri, '@');
      return uri;
    }

    return (
      <li key={currentId} className='mt-1'>
        {filteredRouteList.length > 0 && (
          <>
            <div className='toggler-section mb-2 px-1 bg-gradient rounded d-flex rounded-lg'>
              <label
                className='toggler p-2 text-base d-flex align-items-center gap-1 justify-content-between flex-grow-1'
                onClick={() => handleToggle(currentId)}
              >
                <span className='d-flex align-items-center gap-1'>
                  <Icon icon={`${icon || 'prime:bookmark'}`} />
                  <span>{title}</span>
                </span>
                <Icon icon="bi-chevron-down" />
              </label>
            </div>

            <ul id={currentId} className={`list-unstyled ms-${indent} d-none my-1`}>
              {filteredRouteList.length > 0 && (
                <>
                  {filteredRouteList.map((route, i) => (
                    <li className='link' key={`${i}+${currentId}_${route.uri}`}>
                      <NavLink
                        to={cleanUri(route.uri)}
                        className="form-check-label py-1 text-light text-decoration-none px-3 cursor-pointer d-flex align-items-center gap-1"
                      >
                        <Icon icon={`${route.icon || 'mdi:leads'}`} />
                        <span>{route.title}</span>
                      </NavLink>
                    </li>
                  ))}
                </>
              )}

              {children && children.length > 0 && (
                <li className={`has-dropdown ml-${indent}`}>
                  <ul className='list-unstyled dropdown'>
                    {renderRoutes(children, indent, prevFolderName + '/' + slug)}
                  </ul>
                </li>
              )}
            </ul>
          </>
        )}
      </li>
    );
  });
};

export default MenuRoutesTree;
