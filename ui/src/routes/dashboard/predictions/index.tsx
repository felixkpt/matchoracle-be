import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import Predictions from "../../../Pages/Dashboard/Predictions/Index";

const relativeUri = 'dashboard/predictions/';

const routes = [
    {
        path: ``,
        element: <DefaultLayout uri={relativeUri} permission="" Component={Predictions} />,
    },
    {
        path: `today`,
        element: <DefaultLayout uri={`${relativeUri}today`} permission="" Component={Predictions} />,
    },
    {
        path: `yesterday`,
        element: <DefaultLayout uri={`${relativeUri}yesterday`} permission="" Component={Predictions} />,
    },
    {
        path: `tomorrow`,
        element: <DefaultLayout uri={`${relativeUri}tomorrow`} permission="" Component={Predictions} />,
    },
    {
        path: `:year`,
        element: <DefaultLayout uri={`${relativeUri}:year`} permission="" Component={Predictions} />,
    },
    {
        path: `:year/:month`,
        element: <DefaultLayout uri={`${relativeUri}:year/:month`} permission="" Component={Predictions} />,
    },
    {
        path: `:year/:month/:day`,
        element: <DefaultLayout uri={`${relativeUri}:year/:month/:day`} permission="" Component={Predictions} />,
    },
    {
        path: `:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`,
        element: <DefaultLayout uri={`${relativeUri}:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`} permission="" Component={Predictions} />,
    },
];

export default routes;
