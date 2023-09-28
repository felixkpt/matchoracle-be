import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Statuses from "@/Pages/Admin/Settings/Picklists/Statuses/Default/Index";


const relativeUri = 'settings/picklists/statuses/default/';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri + ''} permission="" Component={Statuses} />,
    },
];

export default index;
