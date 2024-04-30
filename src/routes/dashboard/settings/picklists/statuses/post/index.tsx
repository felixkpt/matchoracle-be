import DefaultLayout from "../../../../../../Layouts/Default/DefaultLayout";
import Statuses from "../../../../../../Pages/Dashboard/Settings/Picklists/Statuses/Post/Index";

const relativeUri = 'dashboard/settings/picklists/statuses/post/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Statuses} />,
    },
];

export default index;
