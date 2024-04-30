import DefaultLayout from "../../../../../Layouts/Default/DefaultLayout";
import GameSources from "../../../../../Pages/Dashboard/Settings/Picklists/GameSources/Index";


const relativeUri = 'dashboard/settings/picklists/game-sources/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={GameSources} />,
    },
];

export default index;
