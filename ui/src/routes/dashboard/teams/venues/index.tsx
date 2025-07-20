import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Venues from "../../../../Pages/Dashboard/Teams/Venues/Index";

const relativeUri = 'dashboard/teams/venues/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Venues} />,
    },
]

export default index