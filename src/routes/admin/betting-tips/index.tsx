import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import BettingTips from "@/Pages/Admin/BettingTips/Index";
import BettingTip from "@/Pages/Admin/BettingTips/View/Index";

const relativeUri = 'admin/betting-tips/';

const routes = [
    {
        path: `view/:id`,
        element: <AuthenticatedLayout uri={`${relativeUri}view/:id`} permission="" Component={BettingTip} />,
    },
    {
        path: ``,
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={BettingTips} />,
    },
    {
        path: `today`,
        element: <AuthenticatedLayout uri={`${relativeUri}today`} permission="" Component={BettingTips} />,
    },
    {
        path: `yesterday`,
        element: <AuthenticatedLayout uri={`${relativeUri}yesterday`} permission="" Component={BettingTips} />,
    },
    {
        path: `tomorrow`,
        element: <AuthenticatedLayout uri={`${relativeUri}tomorrow`} permission="" Component={BettingTips} />,
    },
    {
        path: `:year`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year`} permission="" Component={BettingTips} />,
    },
    {
        path: `:year/:month`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year/:month`} permission="" Component={BettingTips} />,
    },
    {
        path: `:year/:month/:day`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year/:month/:day`} permission="" Component={BettingTips} />,
    },
    {
        path: `:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`,
        element: <AuthenticatedLayout uri={`${relativeUri}:start_year/:start_month/:start_day/to/:end_year/:end_month/:end_day`} permission="" Component={BettingTips} />,
    },
];

export default routes;
