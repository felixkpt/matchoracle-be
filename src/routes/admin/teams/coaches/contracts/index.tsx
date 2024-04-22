import DefaultLayout from "../../../../../Layouts/Default/DefaultLayout";
import Contracts from "@/Pages/Admin/Teams/Coaches/Contracts/Index";

const relativeUri = 'admin/teams/coaches/contracts/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Contracts} />,
    },
]

export default index