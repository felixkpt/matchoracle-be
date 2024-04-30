import CreateOrUpdateUser from "../../../../Pages/Dashboard/Settings/Users/CreateOrUpdateUser"
import Users from '../../../../Pages/Dashboard/Settings/Users/Index';
import Detail from "../../../../Pages/Dashboard/Settings/Users/View/Index"
import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";

const relativeUri = 'dashboard/settings/users/'

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