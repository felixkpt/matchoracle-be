import ScrollToTop from '@/components/ScrollToTop';
import Error403 from '@/Pages/ErrorPages/Error403';
import Loader from '@/components/Loader';
import Error404 from '@/Pages/ErrorPages/Error404';
import { environment } from '@/utils/helpers';
import SideNav from '@/Layouts/Default/SideNav/Index';
import NavBar from '../Shared/Navbar/Index';
import Footer from '../Shared/Footer/Index';
import { HttpVerbsType } from '@/interfaces/UncategorizedInterfaces';
import AlertMessage from '@/components/AlertMessage';
import { publish } from '@/utils/events';
import useRestrictedAccess from '@/hooks/useRestrictedAccess';

interface Props {
    uri: string
    permission?: string | null
    method?: HttpVerbsType
    Component: React.ComponentType
}

const AuthenticatedLayout = ({ uri, permission, method, Component }: Props) => {
    // uri = 'admin'

    const { reloadKey, loadingUser, isAllowed, checked, loadingUserError, loadingRoutePermissions, previousUrl, setReloadKey } = useRestrictedAccess({ uri, permission, method })

    return (
        <div key={reloadKey}>
            <ScrollToTop />
            <>
                <NavBar hideFrom='lg' />
                <div id="layoutWrapper">
                    <div id="sideNav">
                        <SideNav />
                    </div>
                    <div id="mainContent" onClick={() => publish('hideSideNav', 'hide')}>
                        <div className='main-content my-2'>
                            <main className='main-content-inner container-fluid p-3 min-h-100vh position-relative'>
                                {
                                    loadingUser && !isAllowed ?
                                        <div style={{ fontSize: '20px' }}>
                                            <Loader position="absolute" height="100vh" message='Please wait, logging you in...' />
                                        </div>
                                        :
                                        isAllowed === true && checked === true ?
                                            <Component />
                                            :
                                            (
                                                !loadingUserError || loadingUserError == 'Unauthenticated.' ?
                                                    (
                                                        loadingRoutePermissions === false && checked === true ?
                                                            (
                                                                environment === 'local' ?
                                                                    <Error403 previousUrl={previousUrl} currentUrl={location.pathname} setReloadKey={setReloadKey} />
                                                                    :
                                                                    <Error404 previousUrl={previousUrl} currentUrl={location.pathname} setReloadKey={setReloadKey} />
                                                            )
                                                            :
                                                            <div style={{ fontSize: '20px' }}>
                                                                <Loader position="absolute" height="100vh" message='Granting you page access...' />
                                                            </div>
                                                    )
                                                    :
                                                    <><AlertMessage message={loadingUserError} /></>
                                            )
                                }
                            </main>
                        </div>
                        <Footer />
                    </div>
                </div>
            </>
        </div>
    );
};

export default AuthenticatedLayout;
