import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Seasons from "@/Pages/Admin/Seasons/Index";

const relativeUri = 'admin/seasons/';

const index = [

    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Seasons} />,
    },
]

export default index