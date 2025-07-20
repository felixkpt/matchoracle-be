import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Addresses from "../../../../Pages/Dashboard/Teams/Addresses/Index";

const relativeUri = 'dashboard/teams/addresses/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Addresses} />,
    },
]

export default index