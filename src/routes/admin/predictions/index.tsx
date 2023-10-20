import AuthenticatedLayout from "@/Layouts/Authenicated/AuthenticatedLayout";
import Predictions from "@/Pages/Admin/Predictions/Index";
import Prediction from "@/Pages/Admin/Predictions/View/Index";

const relativeUri = 'predictions/';

const routes = [
    {
        path: `view/:id`,
        element: <AuthenticatedLayout uri={`${relativeUri}view/:id`} permission="" Component={Prediction} />,
    },
    {
        path: ``,
        element: <AuthenticatedLayout uri={relativeUri} permission="" Component={Predictions} />,
    },
    {
        path: `today`,
        element: <AuthenticatedLayout uri={`${relativeUri}today`} permission="" Component={Predictions} />,
    },
    {
        path: `yesterday`,
        element: <AuthenticatedLayout uri={`${relativeUri}yesterday`} permission="" Component={Predictions} />,
    },
    {
        path: `tomorrow`,
        element: <AuthenticatedLayout uri={`${relativeUri}tomorrow`} permission="" Component={Predictions} />,
    },
    {
        path: `:year`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year`} permission="" Component={Predictions} />,
    },
    {
        path: `:year/:month`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year/:month`} permission="" Component={Predictions} />,
    },
    {
        path: `:year/:month/:date`,
        element: <AuthenticatedLayout uri={`${relativeUri}:year/:month/:date`} permission="" Component={Predictions} />,
    },
];

export default routes;
