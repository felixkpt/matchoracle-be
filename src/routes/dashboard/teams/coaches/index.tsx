import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Coaches from "../../../../Pages/Dashboard/Teams/Coaches/Index";
import contracts from "@/routes/dashboard/teams/coaches/contracts";

const relativeUri = 'dashboard/teams/coaches/';

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