import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Coaches from "@/Pages/Admin/Teams/Coaches/Index";
import contracts from "@/routes/admin/teams/coaches/contracts";

const relativeUri = 'admin/teams/coaches/';

const index = [
    {
        path: 'contracts',
        children: contracts,
    },
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Coaches} />,
    },
]

export default index