import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Continents from "@/Pages/Admin/Continents/Index";

const relativeUri = 'admin/continents/';

const index = [

    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Continents} />,
    },
]

export default index