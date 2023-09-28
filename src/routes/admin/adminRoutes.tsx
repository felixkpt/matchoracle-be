import Admin from '@/Pages/Admin/Index';
import settings from './settings';
import posts from './posts';
import competitions from './competitions';
import AuthenticatedLayout from '@/Layouts/Authenicated/AuthenticatedLayout';
import Error404 from '@/Pages/ErrorPages/Error404';

const adminRoutes = [
  {
    path: '',
    element: <AuthenticatedLayout uri='admin' permission={null} Component={Admin} />,
  },
  {
    path: 'settings',
    children: settings,
  },
  {
    path: 'competitions',
    children: competitions,
  },{
    path: 'posts',
    children: posts,
  },
  {
    path: '*',
    element: <AuthenticatedLayout uri='error-404' permission={null} Component={Error404} />,
  },
];

export default adminRoutes;
