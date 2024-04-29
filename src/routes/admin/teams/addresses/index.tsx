import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import Addresses from "@/Pages/Admin/Teams/Addresses/Index";

const relativeUri = 'admin/teams/addresses/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Addresses} />,
    },
]

export default index