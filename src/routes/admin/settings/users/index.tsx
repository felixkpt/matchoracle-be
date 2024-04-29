import CreateOrUpdateUser from "../../../../Pages/Admin/Settings/Users/CreateOrUpdateUser"
import Users from '../../../../Pages/Admin/Settings/Users/Index';
import Detail from "../../../../Pages/Admin/Settings/Users/View/Index"
import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";

const relativeUri = 'admin/settings/users/'

const index = [

  {
    path: '',
    element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Users} />,
  },
  {
    path: 'create',
    element: <DefaultLayout uri={relativeUri + 'create'} permission="" Component={CreateOrUpdateUser} />,

  },
  {
    path: 'view/:id/edit',
    element: <DefaultLayout uri={relativeUri + 'view/:id/edit'} permission="" Component={CreateOrUpdateUser} />,
  },
  {
    path: 'view/:id',
    element: <DefaultLayout uri={relativeUri + 'view/:id'} permission="" Component={Detail} />,
  },

]

export default index