import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Countries from "@/Pages/Admin/Countries/Index";
import Country from "@/Pages/Admin/Countries/View/Index";

const relativeUri = 'admin/countries/';

const index = [

    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Countries} />,
    },
    {
        path: 'view/:id',
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Country} />,
    },
]

export default index