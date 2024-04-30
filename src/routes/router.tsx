import { createBrowserRouter } from 'react-router-dom';
import guestRoutes from '@/routes/guest/guestRoutes';
import dashboardRoutes from '@/routes/dashboard/dashboardRoutes';
import userRoutes from '@/routes/user/index';

const router = createBrowserRouter([
  {
    path: '/',
    children: guestRoutes,
  },
  {
    path: 'user',
    children: userRoutes
  },
  {
    path: 'dashboard',
    children: dashboardRoutes,
  },
]);

export default router;
