import DefaultLayout from "../../../../../Layouts/Default/DefaultLayout"
import Index from '../../../../../Pages/Dashboard/Settings/System/JobLogs/Index'

const relativeUri = 'dashboard/settings/system/job-logs/'

const index = [

  {
    path: '',
    element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Index} />,
  },

]

export default index