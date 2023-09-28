import { useMemo } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import Str from '@/utils/Str';
import { Icon } from '@iconify/react/dist/iconify.js';
import MenuTree from './MenuTree';
import RoutesList from './RoutesList';
import Select from 'react-select';
import { useRolePermissionsContext } from '@/contexts/RolePermissionsContext';

const Index = () => {

  const { user } = useAuth()
  const { roles, setCurrentRole, currentRole, userMenu, loadingMenu: loading } = useRolePermissionsContext();

  const memoizeMenu = useMemo(() => {

    return (
      <>
        {
          user && Array.isArray(userMenu) ?
            <ul className="metismenu list-unstyled nested-routes main" id="menu">

              {
                userMenu.map((child: RouteCollectionInterface) => {

                  const { routes, children, icon, folder } = child

                  const shouldShowFirstLevelRoutes = true

                  const shouldShowFolder = routes.length > 0 || children.length > 0

                  const currentId = Str.slug((folder).replace(/^\//, ''));

                  const indent = 2

                  if (shouldShowFolder)

                    return (
                      <div key={currentId}>
                        <a className="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target={`#${currentId}`} aria-expanded="false" aria-controls="collapsePages">
                          <span className='d-flex align-items-center gap-1'>
                            <Icon icon={`${icon || 'prime:bookmark'}`} />
                            <span>{Str.title(Str.afterLast(folder, '/'))}</span>
                          </span>
                          <div className="sb-sidenav-collapse-arrow"><Icon className='arrow-section' icon={`bi-chevron-down`} /></div>
                        </a>
                        <div className="collapse" id={`${currentId}`} aria-labelledby="headingTwo" data-bs-parent="#sidenavAccordion">
                          <nav className="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPages">
                            <RoutesList routes={routes} />
                            {
                              children.length > 0 &&
                              <>
                                {
                                  children.map((child) => <MenuTree key={child.folder} indent={indent} child={child} prevId={currentId} />)
                                }
                              </>
                            }
                          </nav>
                        </div>
                      </div>
                    )

                  else return null
                })
              }
            </ul>
            :
            <div className='ps-2 pt-3'>
              {!currentRole || loading ?
                <div className="d-flex align-items-center gap-3">
                  <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  Loading...
                </div>
                :
                `No menus associated with role.`
              }
            </div>
        }
      </>
    )

  }, [user, userMenu, loading])

  if (currentRole)
    return (
      <nav className="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
        <div className="sb-sidenav-menu shadow">
          <div className="nav pt-2">

            <div className='px-1'>
              <Select
                className="basic-single text-dark mb-2"
                classNamePrefix="select"
                value={currentRole || []}
                isSearchable={true}
                name="roles"
                options={roles}
                getOptionValue={(option) => `${option['id']}`}
                getOptionLabel={(option) => `${option['name']}`}
                onChange={(item) => setCurrentRole(item)}
              />
              {memoizeMenu}
            </div>
          </div>
        </div>
      </nav>
    )
};

export const toggleSidebar = (event: Event, action: string | undefined = undefined) => {

  if (action && action === 'hide') {

    if (document.body.classList.contains('sb-sidenav-toggled')) {
      document.body.classList.remove('sb-sidenav-toggled');
    } 

  } else {
    event.preventDefault();
    document.body.classList.toggle('sb-sidenav-toggled');
  }

  const isToggled = document.body.classList.contains('sb-sidenav-toggled');
  localStorage.setItem('sb|sidebar-toggle', isToggled.toString());
};


export default Index;
