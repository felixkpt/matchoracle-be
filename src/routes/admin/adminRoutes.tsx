import Admin from '@/Pages/Admin/Dashboard/Index';
import AutomationReport from '@/Pages/Admin/Dashboard/AutomationReport/Index';
import settings from './settings';
import posts from './posts';
import continents from './continents';
import countries from './countries';
import competitions from './competitions';
import seasons from './seasons';
import predictions from './predictions';
import bettingtips from './betting-tips';
import matches from './matches';
import teams from './teams';
import odds from './odds';
import DefaultLayout from '../../Layouts/Default/DefaultLayout';
import Error404 from '@/Pages/ErrorPages/Error404';

const adminRoutes = [
  {
    path: '',
    element: <DefaultLayout uri='admin' permission={null} Component={Admin} />,
  },
  {
    path: 'automation-report',
    element: <DefaultLayout uri='admin/automation-report' permission={null} Component={AutomationReport} />,
  },
  {
    path: 'posts',
    children: posts,
  },
  {
    path: 'competitions',
    children: competitions,
  },
  {
    path: 'continents',
    children: continents,
  },
  {
    path: 'countries',
    children: countries,
  },
  {
    path: 'matches',
    children: matches,
  },
  {
    path: 'teams',
    children: teams,
  },
  {
    path: 'predictions',
    children: predictions,
  },
  {
    path: 'betting-tips',
    children: bettingtips,
  },
  {
    path: 'seasons',
    children: seasons,
  },
  {
    path: 'odds',
    children: odds,
  }, {
    path: 'settings',
    children: settings,
  },
  {
    path: '*',
    element: <DefaultLayout uri='error-404' permission={null} Component={Error404} />,
  },
];

export default adminRoutes;
