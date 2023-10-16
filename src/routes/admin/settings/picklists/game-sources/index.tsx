import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import GameSources from "@/Pages/Admin/Settings/Picklists/GameSources/Index";


const relativeUri = 'settings/picklists/game-sources/';

const index = [
    {
        path: '',
        element: <AuthenticatedLayout uri={relativeUri + ''} permission="" Component={GameSources} />,
    },
];

export default index;
