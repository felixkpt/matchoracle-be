import Users from '../../../../Pages/Dashboard/Settings/Users/Index';
import User from "../../../../Pages/Dashboard/Settings/Users/View/Index"
import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";

const relativeUri = 'dashboard/settings/users/'

const index = [

  {
    path: '',
    element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Users} />,
  },
  {
    path: 'view/:id',
    element: <DefaultLayout uri={relativeUri + 'view/:id'} permission="" Component={User} />,
  },

]

export default index