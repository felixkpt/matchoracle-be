import DefaultLayout from "../../../Layouts/Default/DefaultLayout";
import BettingTips from "@/Pages/Admin/BettingTips/Index";
import Stats from "@/Pages/Admin/BettingTips/Stats";
import BettingTip from "@/Pages/Admin/BettingTips/View/Index";

const relativeUri = 'admin/betting-tips/';

const routes = [
    {
        path: `view/:id`,
        element: <DefaultLayout uri={`${relativeUri}view/:id`} permission="" Component={BettingTip} />,
    },
    {
        path: ``,
        element: <DefaultLayout uri={relativeUri} permission="" Component={BettingTips} />,
    },
    {
        path: `today`,
        element: <DefaultLayout uri={`${relativeUri}today`} permission="" Component={BettingTips} />,
    },
    {
        path: `yesterday`,
        element: <DefaultLayout uri={`${relativeUri}yesterday`} permission="" Component={BettingTips} />,
    },
    {
        path: `tomorrow`,
        element: <DefaultLayout uri={`${relativeUri}tomorrow`} permission="" Component={BettingTips} />,
    },
    {
        path: `:year`,
        element: <DefaultLayout uri={`${relativeUri}:year`} permission="" Component={BettingTips} />,
    },
    {
        path: `:year/:month`,
        element: <DefaultLayout uri={`${relativeUri}:year/:month`} permission="" Component={BettingTips} />,
    },
    {
        path: `:year/:month/:day`,
        element: <DefaultLayout uri={`${relativeUri}:year/:month/:day`} permission="" Component={BettingTips} />,
    },
    {
        path: `:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`,
        element: <DefaultLayout uri={`${relativeUri}:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`} permission="" Component={BettingTips} />,
    },
    {
        path: `stats`,
        element: <DefaultLayout uri={`${relativeUri}stats`} permission="" Component={Stats} />,
    },
];

export default routes;
