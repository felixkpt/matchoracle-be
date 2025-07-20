import Admin from '../../Pages/Dashboard/Dashboard/Index';
import AutomationReport from '../../Pages/Dashboard/Dashboard/AutomationReport/Index';
import settings from './settings';
import continents from './continents';
import countries from './countries';
import competitions from './competitions';
import seasons from './seasons';
import predictions from './predictions';
import uploads from './uploads';
import matches from './matches';
import teams from './teams';
import odds from './odds';
import DefaultLayout from '../../Layouts/Default/DefaultLayout';
import Error404 from '@/Pages/ErrorPages/Error404';

const dashboardRoutes = [
  {
    path: '',
    element: <DefaultLayout uri='dashboard' permission={null} Component={Admin} />,
  },
  {
    path: 'automation-report',
    element: <DefaultLayout uri='dashboard/automation-report' permission={null} Component={AutomationReport} />,
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
    path: 'seasons',
    children: seasons,
  },
  {
    path: 'odds',
    children: odds,
  },
  {
    path: 'settings',
    children: settings,
  },
  {
    path: 'uploads',
    children: uploads,
  },
  {
    path: '*',
    element: <DefaultLayout uri='error-404' permission={null} Component={Error404} />,
  },
];

export default dashboardRoutes;
