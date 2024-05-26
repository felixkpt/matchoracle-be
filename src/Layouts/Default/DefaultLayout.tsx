import React from 'react';
import ScrollToTop from '@/components/ScrollToTop';
import Error403 from '@/Pages/ErrorPages/Error403';
import Loader from '@/components/Loader';
import Error404 from '@/Pages/ErrorPages/Error404';
import SideNav from '@/Layouts/Default/SideNav/Index';
import NavBar from '../Shared/Navbar/Index';
import Footer from '../Shared/Footer/Index';
import { environment } from '@/utils/helpers';
import useRoleBasedAccessAccess from '../../hooks/rba/useRoleBasedAccessAccess';
import { publish } from '@/utils/events';
import { HttpVerbsType } from '@/interfaces/UncategorizedInterfaces';

interface Props {
    uri: string;
    permission?: string | null;
    method?: HttpVerbsType;
    Component: React.ComponentType;
}

const AuthenticatedLayout = ({ uri, permission, method, Component }: Props) => {
    method = method || 'get'

    const { loading, isAllowed, shouldRender403, reload, previousUrl } = useRoleBasedAccessAccess({ uri, permission, method });

    return (
        <div>
            <ScrollToTop />
            <NavBar hideFrom="lg" />
            <div id="layoutWrapper">
                <div id="sideNav">
                    <SideNav />
                </div>
                <div id="mainContent" onClick={() => publish('hideSideNav', 'hide')}>
                    <div className="main-content my-2 d-flex flex-column justify-content-between align-items-between">
                        <main className="main-content-inner container-fluid p-3 position-relative min-h-100vh">
                            {loading ? (
                                <div style={{ fontSize: '20px' }}>
                                    <Loader position="absolute" height="100vh" message="Please wait..." />
                                </div>
                            ) : isAllowed ? (
                                <Component />
                            ) : (
                                shouldRender403 ? (
                                    environment === 'local' ?
                                        <Error403 previousUrl={previousUrl} currentUrl={location.pathname} reload={reload} />
                                        :
                                        <Error404 previousUrl={previousUrl} currentUrl={location.pathname} reload={reload} />
                                )
                                    : (
                                        <div style={{ fontSize: '20px' }}>
                                            <Loader position="absolute" height="100vh" message="Granting you page access..." />
                                        </div>
                                    )
                            )}
                        </main>
                    </div>
                    <Footer />
                </div>
            </div>
        </div>
    );
};

export default AuthenticatedLayout;
