import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Competitions from "@/Pages/Admin/Competitions/Index";

const relativeUri = 'competitions/';

const index = [

    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Competitions} />,
    },
]

export default index