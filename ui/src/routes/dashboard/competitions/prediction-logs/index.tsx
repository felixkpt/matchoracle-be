import DefaultLayout from "../../../../Layouts/Default/DefaultLayout";
import PredictionLogs from "../../../../Pages/Dashboard/Competitions/PredictionLogs/Index";

const relativeUri = 'dashboard/competitions/prediction-logs/';

const index = [
    {
        path: '',
        element: <DefaultLayout uri={relativeUri} permission="" Component={PredictionLogs} />,
    }
]

export default index