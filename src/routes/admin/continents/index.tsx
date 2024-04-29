import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Continents from "@/Pages/Admin/Continents/Index";

const relativeUri = 'admin/continents/';

const index = [

    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Continents} />,
    },
]

export default index