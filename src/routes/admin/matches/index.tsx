import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Matches from "@/Pages/Admin/Matches/Index";
import Match from "@/Pages/Admin/Matches/View/Index";

const relativeUri = 'matches/';

const routes = [
    {
        path: `view/:id`,
        element: <AuthenticatedLayout uri={`${relativeUri}view/:id`} permission="" Component={Match} />,
    },
    {
        path: ``,
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Matches} />,
    },
    {
        path: `today`,
        element: <AuthenticatedLayout uri={`${relativeUri}today`} permission="" Component={Matches} />,
    },
    {
        path: `yesterday`,
        element: <AuthenticatedLayout uri={`${relativeUri}yesterday`} permission="" Component={Matches} />,
    },
    {
        path: `tomorrow`,
        element: <AuthenticatedLayout uri={`${relativeUri}tomorrow`} permission="" Component={Matches} />,
    },
    {
        path: `:year`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year`} permission="" Component={Matches} />,
    },
    {
        path: `:year/:month`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year/:month`} permission="" Component={Matches} />,
    },
    {
        path: `:year/:month/:date`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year/:month/:date`} permission="" Component={Matches} />,
    },
];

export default routes;
