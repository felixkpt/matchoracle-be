import { useEffect, useState } from 'react';
import { useAuth } from '@/contexts/AuthContext';
import { useNavigate } from 'react-router-dom';

import useAxios from '@/hooks/useAxios';
import ScrollToTop from '@/components/ScrollToTop';
import Error403 from '@/Pages/ErrorPages/Error403';
import usePermissions from '@/hooks/usePermissions';
import Loader from '@/components/Loader';
import { useRolePermissionsContext } from '@/contexts/RolePermissionsContext';
import Error404 from '@/Pages/ErrorPages/Error404';
import { environment } from '@/utils/helpers';
import SideNav, { toggleSidebar } from '@/Layouts/Default/SideNav/Index';
import NavBar from '../Shared/Navbar/Index';
import Footer from '../Shared/Footer/Index';
import { HttpVerbsType } from '@/interfaces/UncategorizedInterfaces';

interface Props {
    uri: string
    permission?: string | null
    method?: HttpVerbsType
    Component: React.ComponentType
}

const AuthenticatedLayout = ({ uri, permission, method, Component }: Props) => {

    const [reloadKey, setReloadKey] = useState<number>(0);
    const { updateUser, deleteUser, verified, setRedirectTo } = useAuth();
    const navigate = useNavigate();
    const { data, loading: loadingUser, get } = useAxios();
    const { loadingRoutePermissions, currentRole, refreshCurrentRole, setCurrentRole, fetchRoutePermissions, routePermissions } = useRolePermissionsContext();
    const { userCan } = usePermissions();
    const allowedRoutes = ['error-404', 'login'];
    const [isAllowed, setIsAllowed] = useState(false);
    const [checked, setChecked] = useState(false);
    const [tried, setTried] = useState(false);
    const [previousUrl, setPreviousUrl] = useState<string | null>(null);

    // useEffect to check permission and loading state
    useEffect(() => {
        const testPermission = permission || uri;

        if (testPermission && loadingRoutePermissions === false) {
            if (!allowedRoutes.includes(testPermission)) {
                const isAllowed = userCan(testPermission, method || 'get');
                setIsAllowed(isAllowed);
            }

            setTimeout(function () {
                setChecked(true);
            }, 700);
        }
    }, [verified, loadingRoutePermissions, permission, routePermissions]);

    // useEffect to fetch user data and refresh current role
    useEffect(() => {
        const fetchData = () => {
            get('/auth/user?verify=1');
        }

        if (verified === false) {
            setCurrentRole(undefined);
            fetchData();
        }
    }, [verified]);

    useEffect(() => {
        if (currentRole === undefined) {
            refreshCurrentRole();
        }
    }, [currentRole]);

    // useEffect to update user data or delete user on loading state change
    useEffect(() => {
        if (tried === false && loadingUser === true) {
            setTried(true);
        }

        if (loadingUser === false && tried === true) {
            const user = data;
            if (user) {
                updateUser(user);
            } else {
                deleteUser();
            }
        }
    }, [loadingUser, tried]);

    // useEffect to update previous URL
    useEffect(() => {
        if (previousUrl !== location.pathname) {
            setPreviousUrl(location.pathname);
        }
    }, [location.pathname]);

    // useEffect to fetch route permissions on reloadKey change
    useEffect(() => {
        if (reloadKey > 0) {
            fetchRoutePermissions();
        }
    }, [reloadKey]);

    // useEffect to redirect to login if not allowed and loadingUser is false
    useEffect(() => {
        if (!loadingUser && !isAllowed && checked) {
            setRedirectTo(location.pathname);
            navigate('/login');
        }
    }, [loadingUser, isAllowed, checked]);

    return (
        <div key={reloadKey}>
            <ScrollToTop />
            <>
                <NavBar />
                <div id="layoutWrapper">
                    <div id="sideNav">
                        <SideNav />
                    </div>
                    <div id="mainContent"
                        onClick={(e: any) => {
                            if (window.innerWidth < 992) {
                                toggleSidebar(e, 'hide')
                            }
                        }}
                    >
                        <div className='main-content mb-4'>
                            <main className='main-content-inner container-fluid mt-2 px-3 min-h-100vh'>
                                {
                                    isAllowed === true && checked === true ?
                                        <Component />
                                        :
                                        <>
                                            {
                                                loadingRoutePermissions === false && checked === true ? (
                                                    environment === 'local' ? <Error403 previousUrl={previousUrl} currentUrl={location.pathname} setReloadKey={setReloadKey} /> : <Error404 previousUrl={previousUrl} currentUrl={location.pathname} setReloadKey={setReloadKey} />
                                                ) : (
                                                    <Loader message='Granting you page access...' position='static' />
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
            <div className='container-fluid mt-4 px-4'>
                <div className="position-absolute top-50 start-50 translate-middle w-100">
                    <div className="row justify-content-center">
                        <div className="col-5">
                            {
                                loadingUser && !isAllowed ?
                                    <div className="d-flex justify-content-center align-items-center gap-3">
                                        <span className="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Please wait, logging you in...
                                    </div>
                                    :
                                    <>
                                        {
                                            !isAllowed &&
                                            <div className='alert text-center'>
                                                <p>Unknown server error occurred.</p>
                                                <a href={location.pathname} className="link_404 rounded">Reload</a>
                                            </div>
                                        }
                                    </>
                            }
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default AuthenticatedLayout;
