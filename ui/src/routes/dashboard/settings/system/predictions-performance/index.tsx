import DefaultLayout from "../../../../../Layouts/Default/DefaultLayout"
import Index from '../../../../../Pages/Dashboard/Settings/System/PredictionsPerformance/Index'

const relativeUri = 'dashboard/settings/system/predictions-performance/'

const index = [

  {
    path: '',
    element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Index} />,
  },

]

export default index