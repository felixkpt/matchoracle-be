import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Countries from "@/Pages/Admin/Countries/Index";
import Country from "@/Pages/Admin/Countries/View/Index";

const relativeUri = 'admin/countries/';

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