import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Venues from "@/Pages/Admin/Teams/Venues/Index";

const relativeUri = 'admin/teams/venues/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Venues} />,
    },
]

export default index