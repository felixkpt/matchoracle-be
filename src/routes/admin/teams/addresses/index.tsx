import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Addresses from "@/Pages/Admin/Teams/Addresses/Index";

const relativeUri = 'teams/addresses/';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Addresses} />,
    },
]

export default index