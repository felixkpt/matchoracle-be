import Admin from '@/Pages/Admin/Index';
import settings from './settings';
import posts from './posts';
import continents from './continents';
import countries from './countries';
import competitions from './competitions';
import teams from './teams';
import AuthenticatedLayout from '@/Layouts/Authenicated/AuthenticatedLayout';
import Error404 from '@/Pages/ErrorPages/Error404';

const adminRoutes = [
  {
    path: '',
    element: <AuthenticatedLayout uri='admin' permission={null} Component={Admin} />,
  },
  {
    path: 'posts',
    children: posts,
  },
  {
    path: 'countries',
    children: countries,
  },
  {
    path: 'competitions',
    children: competitions,
  },
  {
    path: 'teams',
    children: teams,
  },
  {
    path: 'continents',
    children: continents,
  },
  {
    path: 'settings',
    children: settings,
  },
  {
    path: '*',
    element: <AuthenticatedLayout uri='error-404' permission={null} Component={Error404} />,
  },
];

export default adminRoutes;
