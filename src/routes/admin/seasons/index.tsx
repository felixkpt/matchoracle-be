import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Seasons from "@/Pages/Admin/Seasons/Index";

const relativeUri = 'admin/seasons/';

const index = [

    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Seasons} />,
    },
]

export default index