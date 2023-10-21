import { useEffect, useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { NavLink, useNavigate } from 'react-router-dom';

import Navbar from './Navbar/Index';
import Footer from './Footer/Index';
import useAxios from '@/hooks/useAxios';
import ScrollToTop from '@/components/ScrollToTop';
import Error403 from '@/Pages/ErrorPages/Error403';
import usePermissions from '@/hooks/usePermissions';
import Loader from '@/components/Loader';
import { useRolePermissionsContext } from '@/contexts/RolePermissionsContext';
import Error404 from '@/Pages/ErrorPages/Error404';
import { environment } from '@/utils/helpers';
import SideNav, { toggleSidebar } from '@/Layouts/Authenicated/SideNav/Index';
import { HttpVerbsType } from '@/interfaces/UncategorizedInterfaces';

interface Props {
    uri: string
    permission?: string | null
    method?: HttpVerbsType
    Component: React.ComponentType
}

const AuthenticatedLayout = ({ uri, permission, method, Component }: Props) => {

    const [reloadKey, setReloadKey] = useState<number>(0);

    const { user, updateUser, deleteUser, verified, setRedirectTo } = useAuth();
    const navigate = useNavigate();

    // Initialize useAxios with the desired endpoint for fetching user data
    const { data, loading: loadingUser, get } = useAxios();

    const { loadingRoutePermissions, currentRole, refreshCurrentRole, setCurrentRole, fetchRoutePermissions, routePermissions } = useRolePermissionsContext();

    const { checkPermission } = usePermissions()

    const allowedRoutes = ['error-404']

    const [isAllowed, setIsAllowed] = useState(true)
    const [checked, setChecked] = useState(false)

    useEffect(() => {
        // Prioritize permission is given (this refers to direct permission)
        const testPermission = permission || uri

        if (verified === true && testPermission && loadingRoutePermissions === false) {

            if (!allowedRoutes.includes(testPermission)) {
                const isAllowed = checkPermission(testPermission, method || 'get');
                setIsAllowed(isAllowed);
            }

            setTimeout(function () {
                setChecked(true);
            }, 700);

        }

    }, [verified, loadingRoutePermissions, Component, permission, routePermissions])

    useEffect(() => {

        const fetchData = () => {
            get('/user?verify=1');
        }

        if (verified === false) {
            setCurrentRole(undefined)
            fetchData();
        }

    }, [verified]);

    useEffect(() => {
        if (currentRole === undefined) {
            refreshCurrentRole()
        }
    }, [currentRole])

    const [tried, setTried] = useState(false);

    useEffect(() => {
        if (tried === false && loadingUser === true) setTried(true);

        if (loadingUser === false && tried === true) {
            const user = data;
            if (user) {
                updateUser(user);
            } else {
                deleteUser()
                setRedirectTo(location.pathname)
                navigate('/login');
            }
        }
    }, [loadingUser, tried]);

    const [previousUrl, setPreviousUrl] = useState<string | null>(null)

    useEffect(() => {

        if (previousUrl !== location.pathname) {
            setPreviousUrl(location.pathname)
        }

    }, [location.pathname]);

    useEffect(() => {
        if (reloadKey > 0) {
            fetchRoutePermissions()
        }

    }, [reloadKey])

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
        <div key={reloadKey}>
            <ScrollToTop />
            {user ? (
                <>
                    <Navbar />
                    <div id="layoutWrapper">
                        <div id="sideNav">
                            <SideNav />
                        </div>
                        <div id="mainContent"
                            onClick={(e: any) => {

                                if (window.innerWidth < 992) {
                                    toggleSidebar(e, 'hide')
                                }
                            }
                            }
                        >
                            <div className='main-content mb-4'>
                                <main className='container-fluid mt-4 px-4 min-h-100vh v'>
                                    {
                                        isAllowed === true && checked === true ?
                                            <Component />
                                            :
                                            <>
                                                {
                                                    loadingRoutePermissions === false && checked === true ? (
                                                        environment === 'local' ? <Error403 previousUrl={previousUrl} currentUrl={location.pathname} setReloadKey={setReloadKey} /> : <Error404 previousUrl={previousUrl} currentUrl={location.pathname} setReloadKey={setReloadKey} />
                                                    ) : (
                                                        <Loader message='Granting you page access...' />
                                                    )
                                                }
                                            </>
                                    }
                                </main>
                            </div>

                            <Footer />
                        </div>
                    </div>
                </>
            ) :
                <div className='container-fluid mt-4 px-4 min-h-100vh v'>
                    <div className="position-absolute top-50 start-50 translate-middle w-100">
                        <div className="row justify-content-center">
                            <div className="col-5">

                                {
                                    loadingUser ?
                                            <div className="d-flex justify-content-center align-items-center gap-3">
                                                <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                Please wait, logging you in...
                                            </div>
                                        :

                                        <div className='alert text-center'>
                                            <p>Unknown server error occured.</p>
                                            <a href={location.pathname} className="link_404 rounded">Reload</a>

                                        </div>
                                }
                            </div>
                        </div>
                    </div>
                </div>
            }
        </div>
    );
};

export default AuthenticatedLayout;
