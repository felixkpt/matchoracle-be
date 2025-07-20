import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Matches from "../../../Pages/Dashboard/Matches/Index";
import Match from "../../../Pages/Dashboard/Matches/View/Index";

const relativeUri = 'dashboard/matches/';

const routes = [
    {
        path: `view/:id`,
        element: <DefaultLayout uri={`${relativeUri}view/:id`} permission="" Component={Match} />,
    },
    {
        path: ``,
        element: <DefaultLayout uri={relativeUri} permission="" Component={Matches} />,
    },
    {
        path: `today`,
        element: <DefaultLayout uri={`${relativeUri}today`} permission="" Component={Matches} />,
    },
    {
        path: `yesterday`,
        element: <DefaultLayout uri={`${relativeUri}yesterday`} permission="" Component={Matches} />,
    },
    {
        path: `tomorrow`,
        element: <DefaultLayout uri={`${relativeUri}tomorrow`} permission="" Component={Matches} />,
    },
    {
        path: `:year`,
        element: <DefaultLayout uri={`${relativeUri}:year`} permission="" Component={Matches} />,
    },
    {
        path: `:year/:month`,
        element: <DefaultLayout uri={`${relativeUri}:year/:month`} permission="" Component={Matches} />,
    },
    {
        path: `:year/:month/:date`,
        element: <DefaultLayout uri={`${relativeUri}:year/:month/:date`} permission="" Component={Matches} />,
    },
    {
        path: `:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`,
        element: <DefaultLayout uri={`${relativeUri}:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`} permission="" Component={Matches} />,
    },

];

export default routes;
