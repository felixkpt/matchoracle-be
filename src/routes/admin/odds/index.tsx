import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Odds from "@/Pages/Admin/Odds/Index";
import Odd from "@/Pages/Admin/Odds/View/Index";

const relativeUri = 'admin/odds/';

const routes = [
    {
        path: `view/:id`,
        element: <DefaultLayout uri={`${relativeUri}view/:id`} permission="" Component={Odd} />,
    },
    {
        path: ``,
        element: <DefaultLayout uri={relativeUri} permission="" Component={Odds} />,
    },
    {
        path: `today`,
        element: <DefaultLayout uri={`${relativeUri}today`} permission="" Component={Odds} />,
    },
    {
        path: `yesterday`,
        element: <DefaultLayout uri={`${relativeUri}yesterday`} permission="" Component={Odds} />,
    },
    {
        path: `tomorrow`,
        element: <DefaultLayout uri={`${relativeUri}tomorrow`} permission="" Component={Odds} />,
    },
    {
        path: `:year`,
        element: <DefaultLayout uri={`${relativeUri}:year`} permission="" Component={Odds} />,
    },
    {
        path: `:year/:month`,
        element: <DefaultLayout uri={`${relativeUri}:year/:month`} permission="" Component={Odds} />,
    },
    {
        path: `:year/:month/:day`,
        element: <DefaultLayout uri={`${relativeUri}:year/:month/:day`} permission="" Component={Odds} />,
    },
    {
        path: `:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`,
        element: <DefaultLayout uri={`${relativeUri}:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`} permission="" Component={Odds} />,
    },
];

export default routes;
