import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout"
import Index from '@/Pages/Admin/Settings/System/JobLogs/Index'

const relativeUri = 'settings/system/job-logs/'

const index = [

  {
    path: '',
    element: <AuthenticatedLayout uri={relativeUri + ''} permission="" Component={Index} />,
  },

]

export default index