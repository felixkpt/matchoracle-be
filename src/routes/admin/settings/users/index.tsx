import CreateOrUpdateUser from "../../../../Pages/Admin/Settings/Users/CreateOrUpdateUser"
import Users from '../../../../Pages/Admin/Settings/Users/Index';
import Detail from "../../../../Pages/Admin/Settings/Users/View/Index"
import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";

const relativeUri = 'admin/settings/users/'

const index = [

  {
    path: '',
    element: <AuthenticatedLayout uri={relativeUri + ''} permission="" Component={Users} />,
  },
  {
    path: 'create',
    element: <AuthenticatedLayout uri={relativeUri + 'create'} permission="" Component={CreateOrUpdateUser} />,

  },
  {
    path: 'view/:id/edit',
    element: <AuthenticatedLayout uri={relativeUri + 'view/:id/edit'} permission="" Component={CreateOrUpdateUser} />,
  },
  {
    path: 'view/:id',
    element: <AuthenticatedLayout uri={relativeUri + 'view/:id'} permission="" Component={Detail} />,
  },

]

export default index