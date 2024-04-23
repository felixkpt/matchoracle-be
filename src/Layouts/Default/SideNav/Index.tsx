import { useEffect, useMemo } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import Str from '@/utils/Str';
import { Icon } from '@iconify/react/dist/iconify.js';
import MenuTree from './MenuTree';
import RoutesList from './RoutesList';
import Select from 'react-select';
import { useRolePermissionsContext } from '@/contexts/RolePermissionsContext';
import Loader from '@/components/Loader';

const Index = () => {
  const { user } = useAuth();
  const { roles, setCurrentRole, currentRole, userMenu, expandedRootFolders, loadingMenu: loading } = useRolePermissionsContext();
  
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
        {Array.isArray(userMenu) && userMenu.length > 0 ? (
          <ul className="list-unstyled nested-routes main">
            {userMenu.map((child: RouteCollectionInterface) => {
              const { routes, children, icon, folder } = child;
              const shouldShowFolder = routes.length > 0 || children.length > 0;
              const currentId = Str.slug((folder).replace(/^\//, ''));
              const indent = 2;

              // Check if the folder should be expanded
              const isExpanded = expandedRootFolders.includes(folder);

              if (shouldShowFolder && !isExpanded) {
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
              } else if (shouldShowFolder && isExpanded) {
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
              <Loader justify='start' />
              : `No menus associated with role.`
            }
          </div>
        )}
      </>
    );
  }, [user, userMenu, loading]);

  useEffect(() => {
    const sidebarToggle = document.body.querySelector('#sidebarToggle');

    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', toggleSidebar);
    }

    return () => {
      if (sidebarToggle) {
        sidebarToggle.removeEventListener('click', toggleSidebar);
      }
    };
  }, []);

  return (
    <nav className="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
      <div className="sb-sidenav-menu shadow">
        <div className="nav pt-2">
          <div className='px-1'>
            <div id="menu">
              <div id='role-switcher' title='Switch your role'>
                {
                 user && roles.length > 0 &&
                  <Select
                  className="basic-single text-dark mb-2"
                  classNamePrefix="select"
                  value={currentRole || []}
                  isSearchable={true}
                  name="roles"
                  options={roles}
                  placeholder='Switch your role'
                  getOptionValue={(option: any) => `${option['id']}`}
                  getOptionLabel={(option: any) => `${option['name']}`}
                  onChange={(item: any) => setCurrentRole(item)}
                />
                }
              </div>
              {memoizeMenu}
            </div>
          </div>
        </div>
      </div>
    </nav>
  );
}

export const toggleSidebar = (event?: Event, action: string | undefined = undefined, forceClose = false) => {
  if (window.innerWidth >= 992) return

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
