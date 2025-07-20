import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Countries from "../../../Pages/Dashboard/Countries/Index";
import Country from "../../../Pages/Dashboard/Countries/View/Index";

const relativeUri = 'dashboard/countries/';

const index = [

    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Countries} />,
    },
    {
        path: 'view/:id',
        element: <DefaultLayout uri={relativeUri} permission="" Component={Country} />,
    },
]

export default index