import { useEffect, useMemo } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import Str from '@/utils/Str';
import { Icon } from '@iconify/react/dist/iconify.js';
import MenuTree from './MenuTree';
import RoutesList from './RoutesList';
import Select from 'react-select';
import { useRolePermissionsContext } from '@/contexts/RolePermissionsContext';

const Index = () => {
  const { user } = useAuth();
  const { roles, setCurrentRole, currentRole, userMenu, nestedRoutesFolder, loadingMenu: loading } = useRolePermissionsContext();

  console.log(nestedRoutesFolder)
  
  useEffect(() => {
    const expand = document.body.querySelector('.btn-expand-collapse');
    if (expand) {
      expand.addEventListener('click', collapseEvent);
    }

    return () => removeEventListener('click', collapseEvent);
  }, [currentRole]);

  function collapseEvent() {
    const trg = document.body.querySelector('#layoutWrapper');

    if (trg) {
      trg.classList.toggle('collapsed');

      const isToggled = trg.classList.contains('collapsed');
      localStorage.setItem('sb|sidebar-collapse', isToggled.toString());
    }
  }

  useEffect(() => {
    const exists = localStorage.getItem('sb|sidebar-collapse');
    if (exists) {
      const collaped = JSON.parse(exists);
      if (collaped === true) {
        collapseEvent();
      }
    }
  }, []);

  const memoizeMenu = useMemo(() => {
    return (
      <>
        {user && Array.isArray(userMenu) ? (
          <ul className="list-unstyled nested-routes main">
            {userMenu.map((child: RouteCollectionInterface) => {
              const { routes, children, icon, folder } = child;
              const shouldShowFolder = routes.length > 0 || children.length > 0;
              const currentId = Str.slug((folder).replace(/^\//, ''));
              const indent = 2;

              if (shouldShowFolder && folder !== nestedRoutesFolder) {
                return (
                  <div key={currentId} className='position-relative top-0 first-level'>
                    <a className="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target={`#${currentId}`} aria-expanded="false" aria-controls="collapsePages">
                      <span className='d-flex align-items-center gap-1'>
                        <Icon className='nav-icon' icon={`${icon || 'prime:bookmark'}`} />
                        <span className='nav-label'>{Str.title(Str.afterLast(folder, '/'))}</span>
                      </span>
                      <div className="sb-sidenav-collapse-arrow"><Icon className='arrow-section' icon={`bi-chevron-down`} /></div>
                    </a>
                    <div className="collapse position-fixed-collaped sb-sidenav-light" id={`${currentId}`} aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                      <nav className="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                        <RoutesList routes={routes} />
                        {children.length > 0 && children.map((child) => <MenuTree key={child.folder} indent={indent} child={child} prevId={currentId} />)}
                      </nav>
                    </div>
                  </div>
                );
              } else if (folder === nestedRoutesFolder) {
                return (
                  <div key={currentId}>
                    <RoutesList routes={routes} />
                    {children.length > 0 && children.map((child) => <MenuTree key={child.folder} indent={indent} child={child} prevId={currentId} />)}
                  </div>
                );
              } else {
                return null;
              }
            })}
          </ul>
        ) : (
          <div className='ps-2 pt-3'>
            {!currentRole || loading ?
              <div className="d-flex align-items-center gap-3">
                <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                Loading...
              </div>
              : `No menus associated with role.`
            }
          </div>
        )}
      </>
    );
  }, [user, userMenu, loading]);

  if (currentRole) {
    return (
      <nav className="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
        <div className="sb-sidenav-menu shadow">
          <div className="nav pt-2">
            <div className='px-1'>
              <div id="menu">
                <div id='role-switcher'>
                  <Select
                    className="basic-single text-dark mb-2"
                    classNamePrefix="select"
                    value={currentRole || []}
                    isSearchable={true}
                    name="roles"
                    options={roles}
                    getOptionValue={(option: any) => `${option['id']}`}
                    getOptionLabel={(option: any) => `${option['name']}`}
                    onChange={(item: any) => setCurrentRole(item)}
                  />
                </div>
                {memoizeMenu}
              </div>
            </div>
          </div>
        </div>
      </nav>
    );
  }
};

export const toggleSidebar = (event?: Event, action: string | undefined = undefined, forceClose = false) => {
  if (action && action === 'hide') {
    if (document.body.classList.contains('sb-sidenav-toggled')) {
      document.body.classList.remove('sb-sidenav-toggled');
    }
    if (forceClose) {
      document.body.classList.add('sb-sidenav-toggled');
    }
  } else if (event) {
    event.preventDefault();
    document.body.classList.toggle('sb-sidenav-toggled');
  }

  const isToggled = document.body.classList.contains('sb-sidenav-toggled');
  localStorage.setItem('sb|sidebar-toggle', isToggled.toString());
};

export default Index;
