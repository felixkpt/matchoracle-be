import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Venues from "@/Pages/Admin/Teams/Venues/Index";

const relativeUri = 'admin/teams/venues/';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Venues} />,
    },
]

export default index