import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Coaches from "@/Pages/Admin/Teams/Coaches/Index";

const relativeUri = 'admin/teams/coaches/';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Coaches} />,
    },
]

export default index