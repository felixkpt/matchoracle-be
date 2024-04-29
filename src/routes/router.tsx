import { createBrowserRouter } from 'react-router-dom';
import guestRoutes from '@/routes/guest/guestRoutes';
import adminRoutes from '@/routes/admin/adminRoutes';
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
    path: 'admin',
    children: adminRoutes,
  },
]);

export default router;
