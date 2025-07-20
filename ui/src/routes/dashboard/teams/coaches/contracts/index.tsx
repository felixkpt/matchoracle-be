import DefaultLayout from "../../../../../Layouts/Default/DefaultLayout";
import Contracts from "../../../../../Pages/Dashboard/Teams/Coaches/Contracts/Index";

const relativeUri = 'dashboard/teams/coaches/contracts/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Contracts} />,
    },
]

export default index