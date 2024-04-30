import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Continents from "../../../Pages/Dashboard/Continents/Index";

const relativeUri = 'dashboard/continents/';

const index = [

    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Continents} />,
    },
]

export default index