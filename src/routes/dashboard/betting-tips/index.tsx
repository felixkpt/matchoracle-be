import DefaultLayout from "@/Layouts/Default/DefaultLayout";
import BettingTips from "@/Pages/Dashboard/BettingTips/Index";
import HowToSubscribe from "@/Pages/Dashboard/BettingTips/HowToSubscribe";
import Stats from "@/Pages/Dashboard/BettingTips/Stats";

const relativeUri = 'dashboard/betting-tips/';

const routes = [
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
    {
        path: `how-to-subscribe`,
        element: <DefaultLayout uri={`${relativeUri}`} permission="" Component={HowToSubscribe} />,
    },
];

export default routes;
