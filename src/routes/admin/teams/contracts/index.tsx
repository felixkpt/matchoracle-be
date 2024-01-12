import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Contracts from "@/Pages/Admin/Teams/Contracts/Index";

const relativeUri = 'admin/teams/contracts/';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Contracts} />,
    },
]

export default index