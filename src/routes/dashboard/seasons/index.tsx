import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Seasons from "../../../Pages/Dashboard/Seasons/Index";

const relativeUri = 'dashboard/seasons/';

const index = [

    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Seasons} />,
    },
]

export default index