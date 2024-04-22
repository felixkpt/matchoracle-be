import Str from '@/utils/Str';
import { Icon } from '@iconify/react/dist/iconify.js';
import React from 'react';
import { NavLink } from 'react-router-dom';

interface Props {
  routes: Routes;
  handleSubmit: (checkboxStates: any[]) => void; // Update the type of handleSubmit accordingly
}

function handleToggle(key: string) {
  const target = document.getElementById(key);
  target?.classList.toggle('d-none');
}

const MenuRoutesTree: React.FC<Props> = ({ routes, handleSubmit }) => {
  return (
    <ul className='list-unstyled nested-routes main'>
      {renderRoutes(routes, 0, '')}
    </ul>
  );
};

const renderRoutes = (
  routes: Routes,
  indent = 0,
  prevFolderName = ''
) => {
  indent += 2;

  return Object.keys(routes).map((key) => {
    let { children, routes: routeList } = routes[key];
    // const routeList = children?.routes || [];

    if (routeList.length < 1 && children?.children?.routes?.length < 1) return null

    let others;
    if (children) {
      const { routes: unwanted, ...rest } = children;
      others = rest;
    }

    children = others;

    const currentId = Str.slug((prevFolderName + '/' + key).replace(/^\//, '')) + '_nav';

    const isResolvableURI = (uri: string) => {
      const urisWithParams = uri.split('|').filter(uri => uri.includes('{'));
      const hasGetOrHead = uri.includes('@GET') || uri.includes('@HEAD');
      return urisWithParams.length === 0 && hasGetOrHead;
    };

    const filteredRouteList = (routeTest) => {
      if (routeTest?.length > 0)
        return routeTest?.filter((route) => isResolvableURI(route.uri) && !route.hidden)
      else return []
    }

    function cleanUri(uri) {

      uri = `${uri.startsWith('admin') ? '' : 'admin/'}${uri}`

      uri = Str.before(uri, '@')

      return uri

    }


    const filteredRoutes = filteredRouteList(routeList)

    return (
      <li key={currentId} className='mt-1'>
        {
          filteredRoutes.length > 0 &&
          <>
            <div className='toggler-section mb-2 px-1 bg-gradient rounded d-flex rounded-lg'>
              <label className='toggler p-2 text-base d-flex align-items-center gap-1 justify-content-between flex-grow-1' onClick={() => handleToggle(currentId)}>
                <span className='d-flex align-items-center gap-1'>
                  <Icon icon={`${routes[key].icon || 'prime:bookmark'}`} />
                  <span>{routes[key].title}</span>
                </span>
                <Icon icon="bi-chevron-down" />
              </label>
            </div>

            <ul id={currentId} className={`list-unstyled ms-${indent} d-none my-1`}>
              {filteredRoutes.length > 0 && (
                <>
                  {filteredRoutes.map((route, i) => (
                    <li className='link' key={`${i}+${key}_${route.uri}`}>
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

              {children && Object.keys(children).length > 0 && (
                <li className={`has-dropdown ml-${indent}`}>
                  <ul className='list-unstyled dropdown'>
                    {renderRoutes(children, indent, prevFolderName + '/' + key)}
                  </ul>
                </li>
              )}
            </ul>
          </>
        }
      </li>
    );
  });
};

export default MenuRoutesTree;
