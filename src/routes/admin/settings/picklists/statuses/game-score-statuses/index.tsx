import DefaultLayout from "../../../../../../Layouts/Default/DefaultLayout";
import Statuses from "@/Pages/Admin/Settings/Picklists/Statuses/GameScoreStatus/Index";

const relativeUri = 'admin/settings/picklists/statuses/game-score-statuses/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={Statuses} />,
    },
];

export default index;
