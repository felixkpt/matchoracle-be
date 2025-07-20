import DefaultLayout from "../../../../../../Layouts/Default/DefaultLayout";
import Statuses from "../../../../../../Pages/Dashboard/Settings/Picklists/Statuses/Default/Index";


const relativeUri = 'dashboard/settings/picklists/statuses/default/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Statuses} />,
    },
];

export default index;
