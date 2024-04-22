import DefaultLayout from "../../../../../Layouts/Default/DefaultLayout";
import GameSources from "@/Pages/Admin/Settings/Picklists/GameSources/Index";


const relativeUri = 'admin/settings/picklists/game-sources/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri + ''} permission="" Component={GameSources} />,
    },
];

export default index;
