import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Odds from "@/Pages/Admin/Odds/Index";
import Odd from "@/Pages/Admin/Odds/View/Index";

const relativeUri = 'admin/odds/';

const routes = [
    {
        path: `view/:id`,
        element: <AuthenticatedLayout uri={`${relativeUri}view/:id`} permission="" Component={Odd} />,
    },
    {
        path: ``,
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Odds} />,
    },
    {
        path: `today`,
        element: <AuthenticatedLayout uri={`${relativeUri}today`} permission="" Component={Odds} />,
    },
    {
        path: `yesterday`,
        element: <AuthenticatedLayout uri={`${relativeUri}yesterday`} permission="" Component={Odds} />,
    },
    {
        path: `tomorrow`,
        element: <AuthenticatedLayout uri={`${relativeUri}tomorrow`} permission="" Component={Odds} />,
    },
    {
        path: `:year`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year`} permission="" Component={Odds} />,
    },
    {
        path: `:year/:month`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year/:month`} permission="" Component={Odds} />,
    },
    {
        path: `:year/:month/:day`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year/:month/:day`} permission="" Component={Odds} />,
    },
    {
        path: `:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`,
        element: <AuthenticatedLayout uri={`${relativeUri}:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`} permission="" Component={Odds} />,
    },
];

export default routes;
