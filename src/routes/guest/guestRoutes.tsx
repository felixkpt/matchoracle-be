import Admin from '../../Pages/Dashboard/Dashboard/Index';
import Login from '@/Pages/Auth/Login';
import Register from '@/Pages/Auth/Register';
import Password from '@/Pages/Auth/Password';
import Error404 from '@/Pages/ErrorPages/Error404';
import PasswordSet from '@/Pages/Auth/PasswordSet';
import DefaultLayout from '../../Layouts/Default/DefaultLayout';
import GuestLayout from '../../Layouts/Guest/GuestLayout';
import PrivacyPolicy from '@/Pages/PrivacyPolicy';
import TermsAndConditions from '@/Pages/TermsAndConditions';

const guestRoutes = [
    {
        path: '/',
        element: <DefaultLayout uri='dashboard' permission={null} Component={Admin} />,
    },
    {
        path: '/login',
        element: <GuestLayout Component={Login} />,
    },
    {
        path: '/register',
        element: <GuestLayout Component={Register} />,
    },
    {
        path: '/password',
        element: <GuestLayout Component={Password} />,
    },
    {
        path: '/password-set/:token',
        element: <GuestLayout Component={PasswordSet} />,

    },
    {
        path: '/privacy-policy',
        element: <GuestLayout Component={PrivacyPolicy} />,
    },
    {
        path: '/terms-and-conditions',
        element: <GuestLayout Component={TermsAndConditions} />,
    },
    {
        path: '*',
        element: <GuestLayout Component={Error404} />,
    },
]

export default guestRoutes